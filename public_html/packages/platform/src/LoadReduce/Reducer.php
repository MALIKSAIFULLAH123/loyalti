<?php

namespace MetaFox\Platform\LoadReduce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Facades\User;

class Reducer
{
    /**
     * @var array
     */
    private array $parameters;

    /**
     * @var array
     */
    private array $reducers = [];

    /**
     * @var Store
     */
    private Store $store;

    /**
     * @var Collection
     */
    private Collection $bag;

    /**
     * @var Collection
     */
    private Collection $entityObjs;

    /**
     * @var array<string,boolean>
     */
    private array $existingEntities = [];

    /**
     * Prevent duplicate keys.
     * @var array
     */
    private array $existingKeys = [];

    private bool $disabled;

    /**
     * @var array<string,bool>
     */
    private array $traitMap = [];

    private \Psr\Log\LoggerInterface $logger;

    private bool $enableDebug;

    public function __construct()
    {
        $this->bag = new Collection();
        $this->entityObjs = new Collection();
        $this->store = new Store();
        $this->disabled = !config('app.enable_load_reduce');
        $this->enableDebug = (bool) config('app.enable_profiler');
        $request = request();
        if($this->enableDebug && !$this->disabled && $request->headers->has("x-profiling")){
            $name = str_replace('/', '_', trim($request->getRequestUri() ?? '', '/'));
            $this->logger = Log::build([
                'driver' => 'single',
                'path'   => storage_path('logs/'.$name.'.reducer.log'),
            ]);
        }else {
            $this->logger = Log::channel('profiler');
        }
    }

    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function disable()
    {
        $this->disabled = true;
    }

    public function enable()
    {
        $this->disabled = false;
    }

    /**
     * @param  mixed                                                      $data
     * @return void
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function capture(mixed $data): void
    {
        if ($this->disabled) {
            return;
        }

        $captureStart = microtime(true);

        if (is_array($data) && array_key_exists('data', $data)) {
            $data = $data['data'];
        }

        $entityType = null;
        $listing    = true;
        $items      = null;

        if ($data instanceof ResourceCollection) {
            $items = $data->resource->map(fn ($item) => $item->resource);
        } elseif ($data instanceof JsonResource) {
            $items   = new Collection([$data->resource]);
            $listing = false;
        }

        if (!$items || $items->isEmpty()) {
            return;
        }

        if ($items->count()) {
            $items = $items->filter(fn ($x) => $x instanceof Entity && $x->exists);
        }

        if ($items->count()) {
            $first = $items->first();
            if (is_object($first) && method_exists($first, 'entityType')) {
                $entityType = $first->entityType();
            }
        }

        if (!$items->count()) {
            return;
        }

        $request = app('request');
        $this->with([
            'request'    => $request,
            'context'    => Auth::guest() ? User::getGuestUser() : user(),
            'entityType' => $entityType,
            'item'       => $items->first(),
            'items'      => $items,
            'listing'    => $listing,
            'reducer'    => $this,
        ]);

        $this->loadMissing();

        defined('REDUCER_TIME') or define('REDUCER_TIME', microtime(true) - $captureStart);
    }

    /**
     * @param  array $reducers
     * @return void
     */
    public function register(array $reducers): void
    {
        foreach ($reducers as $reducer) {
            $this->reducers[] = $reducer;
        }
    }

    /**
     * @param  array $parameters
     * @return void
     */
    public function with(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @param  array $values
     * @return void
     */
    public function putMany(array $values): void
    {
        if ($this->disabled) {
            return;
        }

        $this->store->putMany($values);
    }

    /**
     * @return void
     */
    private function loadMissing(): void
    {
        $caps = (new Collection($this->reducers));

        $app  = app();
        $caps = $caps->map(function ($x) {
            try {
                return resolve($x);
            } catch (\Throwable) {
                // do nothing
            }
        })
            ->reject(fn ($x) => method_exists($x, 'reject') && $app->call([$x, 'reject'], $this->parameters));

        $total = 0;

        collect(['before', 'handle', 'after', 'terminate'])
            ->each(function ($method) use ($caps, $app, &$total) {
                $caps->filter(fn ($x) => method_exists($x, $method))
                    ->each(function ($x) use ($app, $method, &$total) {
                        $start = microtime(true);

                        $values = $app->call([$x, $method], $this->parameters);
                        $end    = microtime(true);
                        $total += $end - $start;

                        if ($this->enableDebug) {
                            $this->logger->debug(sprintf(
                                '%s::%s spent %.2f ms',
                                $x::class,
                                $method,
                                1000 * ($end - $start)
                            ));
                        }

                        if (!is_array($values) || empty($values)) {
                            return;
                        }

                        $this->store->putMany($values);

                        if ($this->enableDebug) {
                            $this->logger->debug(json_encode($values, JSON_PRETTY_PRINT));
                        }
                    });
            });
        $this->logger->debug(sprintf('total reducer %.2f', 1000 * $total));
    }

    public function entityKey($type, $id)
    {
        return sprintf('entity::%s-%s', $type, $id);
    }

    public function hasEntity($type, $id)
    {
        return array_key_exists($this->entityKey($type, $id), $this->existingEntities);
    }

    /**
     * @param        $type
     * @param        $id
     * @param        $callback
     * @return mixed
     */
    public function getEntity($type, $id, $callback = null)
    {
        if ($this->disabled) {
            if ($callback) {
                return $callback();
            }

            return null;
        }

        if (!$callback) {
            $callback = fn () => $this->findMissingEntity($type, $id);
        }

        return $this->remember($this->entityKey($type, $id), $callback);
    }

    // do not add to reducer here.

    /**
     * @param  string     $name
     * @param  mixed|null $callback
     * @return mixed
     */
    public function get(string $name, mixed $callback = null): mixed
    {
        if (!$this->disabled && $this->store->has($name)) {
            return $this->store->get($name);
        }

        if (!$callback) {
            return null;
        }

        if ($this->enableDebug) {
            $this->logger->warning(sprintf('get missing %s', $name));
        }

        if ($callback instanceof \Closure) {
            return $callback();
        }

        return $callback;
    }

    // do not add to reducer here.

    /**
     * @param  string $name
     * @param  mixed  $callback
     * @return mixed
     */
    public function remember(string $name, mixed $callback): mixed
    {
        if (!$this->disabled && $this->store->has($name)) {
            return $this->store->get($name);
        }

        $data = $callback instanceof \Closure ? $callback() : $callback;

        if ($this->enableDebug) {
            $this->logger->warning(sprintf('missing %s', $name));
        }

        if (!$this->disabled) {
            $this->store->put($name, $data);
        }

        return $data;
    }

    public function entities(): Collection
    {
        return $this->entityObjs;
    }

    /**
     * @param  Entity $entity
     * @param  bool   $overwrite
     * @return void
     */
    public function addEntity($entity, $overwrite = false): void
    {
        if ($this->disabled || !$entity) {
            return;
        }

        // keep only restarted.
        $type = $entity instanceof UserEntity ? 'user_entity' : $entity->entityType();
        $id   = $entity->entityId();

        $key = $this->entityKey($type, $id);

        if (!$this->store->has($key) || $overwrite) {
            $this->store->put($key, $entity);
        }

        $this->add('entities', $type, $id);

        if (array_key_exists($key, $this->existingEntities)) {
            return;
        }

        // add to objects
        if ($this->enableDebug) {
            // $this->logger->debug(sprintf('add %s', $key));
        }

        $this->existingEntities[$key] = true;
        $this->entityObjs->add($entity);

        if ($entity instanceof UserContract) {
            $this->add('users', $type, $id);
        }

        $className = $entity::class;

        if ($entity instanceof Content) {
            $this->add('users', $entity->userType(), $entity->userId());
            $this->add('users', $entity->ownerType(), $entity->ownerId());
        } else {
            if ($this->hasTrait($className, HasUserMorph::class)) {
                $this->add('users', $entity->userType(), $entity->userId());
            }

            if ($this->hasTrait($className, HasOwnerMorph::class)) {
                $this->add('users', $entity->ownerType(), $entity->ownerId());
            }
        }

        if ($entity->avatar_file_id) {
            $this->add('files', 'file', $entity->avatar_file_id);
        }

        if ($entity->cover_file_id) {
            $this->add('files', 'file', $entity->cover_file_id);
        }

        if ($entity->song_file_id) {
            $this->add('files', 'file', $entity->song_file_id);
        }

        if ($entity->image_file_id) {
            $this->add('files', 'file', $entity->image_file_id);
        }
    }

    private function hasTrait($className, $traitName): bool
    {
        $key = sprintf('%s:u%s', $className, $traitName);

        if (!array_key_exists($key, $this->traitMap)) {
            $this->traitMap[$key] = in_array($traitName, class_uses($className), true);
        }

        return $this->traitMap[$key];
    }

    /**
     * @param  string $cacheId
     * @param  mixed  $value
     * @return void
     */
    public function put(string $cacheId, mixed $value): void
    {
        if ($this->disabled) {
            return;
        }

        // keep only restarted.
        $this->store->put($cacheId, $value);
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->store->flush();
    }

    /**
     * @param  string $ns
     * @param  string $type
     * @param  mixed  $value
     * @return void
     */
    private function add(string $ns, string $type, mixed $value)
    {
        if ($this->disabled) {
            return;
        }
        $key = "{$ns}::{$type}:{$value}";
        if (array_key_exists($key, $this->existingKeys)) {
            return;
        }

        $this->existingKeys[$key] = true;

        if ($this->enableDebug) {
            $this->logger->debug("add $type: $value");
        }

        $this->bag->add([
            'key'   => $key,
            'ns'    => $ns,
            'type'  => $type,
            'value' => $value,
        ]);
    }

    /**
     * @param  string     $ns
     * @param  string     $type
     * @return Collection
     */
    public function collect(string $ns, string $type = '*')
    {
        return $this->bag
            ->filter(fn (
                $x
            ) => ($ns == '*' || $x['ns'] == $ns) && ($type == '*' || $x['type'] == $type));
    }

    /**
     * @param  string     $ns
     * @param  string     $type
     * @return Collection
     */
    public function values(string $ns, string $type = '*'): Collection
    {
        return $this->collect($ns, $type)->map(fn ($x) => $x['value']);
    }

    /**
     * @param  string     $ns
     * @param  string     $type
     * @return Collection
     */
    public function lists(string $ns, string $type = '*'): Collection
    {
        return $this->collect($ns, $type);
    }

    /**
     * @param  string $ns
     * @param  string $type
     * @return array
     */
    public function types(string $ns, string $type = '*'): array
    {
        return $this->collect($ns, $type)
            ->reduce(function ($carry, $x) {
                $carry[$x['type']][] = $x['value'];

                return $carry;
            }, []);
    }

    /**
     * @param  string $type
     * @param  string $id
     * @return void
     */
    private function findMissingEntity($type, $id)
    {
        /** @var Model $modelClass */
        $modelClass = Relation::getMorphedModel($type);

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($id);
    }

    public function loadMissingEntities($type, Collection $ids): void
    {
        if ($this->enableDebug) {
            $this->logger->debug(__FUNCTION__, $ids->all());
        }
        $need = $ids->filter(fn ($id) => !$this->hasEntity($type, $id));

        if ($need->isEmpty()) {
            return;
        }

        /** @var Model $modelClass */
        $modelClass = Relation::getMorphedModel($type);

        if (!$modelClass) {
            return;
        }

        $modelClass::query()->whereIn('id', $need->all())
            ->get()
            ->each(fn ($x) => $this->addEntity($x));
    }
}
