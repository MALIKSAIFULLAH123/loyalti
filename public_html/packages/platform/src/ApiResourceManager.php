<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MetaFox\Core\Constants;
use MetaFox\Core\Http\Resources\v1\Error\Forbidden;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;
use MetaFox\User\Models\UserEntity;

/**
 * Class ApiResourceManager.
 */
class ApiResourceManager
{
    /**
     * @var string
     */
    protected string $version = 'v1';

    /**
     * @var string
     */
    protected string $majorVersion = 'v1';

    /**
     * @var array<string, array<string, array<string,string>>>
     */
    protected array $container = [];

    private \Illuminate\Contracts\Cache\Repository $cacheStore;

    private \Illuminate\Http\Request $request;

    /**
     * ApiResourceManager constructor.
     *
     * @ignore
     */
    public function __construct()
    {
        $this->loadResourceVersions();
        $this->cacheStore = Cache::store('array');
        $this->request    = app('request');
    }

    private function loadResourceVersions(): void
    {
        if (!config('app.mfox_installed')) {
            return;
        }

        $this->container = localCacheStore()->rememberForever('loadResourceVersions.v0', function () {
            $response = [];
            $drivers  = resolve(DriverRepositoryInterface::class)
                ->loadDrivers(
                    Constants::DRIVER_TYPE_JSON_RESOURCE,
                    null,
                    true,
                    null
                );

            foreach ($drivers as $driver) {
                [$name, $value, $version] = $driver;
                Arr::set($response, sprintf('%s.%s', $name, $version), $value);
            }

            return $response;
        });
    }

    /**
     * @return string
     */
    public function getMajorVersion(): string
    {
        return $this->majorVersion;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param ?string $version
     *
     * @link \MetaFox\Platform\Middleware\ApiVersion
     */
    public function setVersion(?string $version): void
    {
        if (!$version) {
            return;
        }

        $this->version      = $version;
        $this->majorVersion = (string) preg_replace("#(v\d+)(.+)#", '$1', $version);
    }

    /**
     * @param string $type
     * @param string $variant
     * @param string $version
     *
     * @return string
     */
    private function pickHttpResourceVersion(string $type, string $variant, string $version): string
    {
        $data = Arr::get($this->container, sprintf('%s.%s', $type, $variant));

        if (!$data) {
            return '';
        }

        ksort($data);

        $foundClass    = '';
        $secondVersion = substr($version, 1);

        foreach ($data as $ver => $foundClass) {
            $firstVersion = substr($ver, 1);
            if (version_compare($firstVersion, $secondVersion, '>=')) {
                return $foundClass;
            }
        }

        return $foundClass;
    }

    /**
     * @var array<string,mixed>
     */
    private $cached = [];

    /**
     * @param string $type
     * @param string $variant
     *
     * @return string
     */
    public function getHttpResourceClass(string $type, string $variant): string
    {
        $storeId = sprintf('%s.%s.%s', $type, $variant, $this->version);

        if (array_key_exists($storeId, $this->cached)) {
            return $this->cached[$storeId];
        }

        $foundClass = $this->pickHttpResourceVersion($type, $variant, $this->version);

        $this->cached[$storeId] = $foundClass;

        return $foundClass;
    }

    /**
     * @param  mixed                   $entity
     * @param  mixed                   $checkPrivacy
     * @return JsonResource|null
     * @throws AuthenticationException
     */
    public function asEmbed($entity, mixed $checkPrivacy = 'view'): ?JsonResource
    {
        return $this->asResource($entity, 'embed', $checkPrivacy);
    }

    /**
     * @param  mixed                   $entity
     * @param  mixed                   $checkPrivacy
     * @return JsonResource|null
     * @throws AuthenticationException
     */
    public function asItem(mixed $entity, mixed $checkPrivacy = true): ?JsonResource
    {
        return $this->asResource($entity, 'item', $checkPrivacy);
    }

    /**
     * @param  mixed                   $entity
     * @param  bool                    $checkPrivacy
     * @return JsonResource|null
     * @throws AuthenticationException
     */
    public function asDetail(mixed $entity, mixed $checkPrivacy = 'view'): ?JsonResource
    {
        return $this->asResource($entity, 'detail', $checkPrivacy);
    }

    /**
     * @param string[] $versions
     *
     * @return string|null
     */
    public function pickNearestVersion(array $versions): ?string
    {
        sort($versions);

        $secondVersion = substr($this->version, 1);

        foreach ($versions as $version) {
            if (version_compare(substr($version, 1), $secondVersion, '>=')) {
                return $version;
            }
        }

        return array_pop($versions);
    }

    /**
     * @param  mixed|null              $entity
     * @param  string                  $variant      item, embed, detail
     * @param  mixed                   $checkPrivacy
     * @return JsonResource|null
     * @throws AuthenticationException
     */
    public function asResource($entity, string $variant, mixed $checkPrivacy = 'view'): ?JsonResource
    {
        $class = null;

        if ($entity instanceof Entity) {
            $class = $this->getHttpResourceClass($entity->entityType(), $variant);
        } elseif ($entity instanceof UserEntity) {
            /**
             * Support for custom work override.
             */
            $model = resolve(UserEntityDetail::class, [
                'resource' => $entity,
            ]);

            $class = get_class($model);
        }

        if (!class_exists($class)) {
            return null;
        }

        if ($checkPrivacy && $entity instanceof Entity) {
            if (!user()->can($checkPrivacy, [$entity, $entity])) {
                $class = Forbidden::class;
            }
        }

        return new $class($entity);
    }

    /**
     * @param                                            $entity
     * @param  string                                    $variant
     * @param  mixed                                     $checkPrivacy
     * @return array|null
     * @throws AuthenticationException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function asJson($entity, string $variant, mixed $checkPrivacy = 'view'): ?array
    {
        $type = $entity instanceof UserEntity ? 'user_entity' : $entity->entityType();

        $cacheKey = sprintf('asJson.%s.%s.%s', $type, $entity->entityId(), $variant);

        $data = $this->cacheStore->get($cacheKey);

        if ($data) {
            return $data;
        }

        $resource = $this->asResource($entity, $variant, $checkPrivacy);

        $data = $resource?->toArray($this->request);
        $this->cacheStore->set($cacheKey, $data);

        return $data;
    }

    /**
     * @param  mixed                                                             $entity
     * @param  bool                                                              $checkPrivacy
     * @return array|null
     * @throws AuthenticationException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function detail(mixed $entity, mixed $checkPrivacy = 'view'): ?array
    {
        if (!$entity) {
            return null;
        }

        return $this->asJson($entity, 'detail', $checkPrivacy);
    }

    /**
     * @param  mixed                                                             $entity
     * @param  bool                                                              $checkPrivacy
     * @return array|null
     * @throws AuthenticationException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function user(mixed $entity, mixed $checkPrivacy = false): ?array
    {
        if (!$entity) {
            return (new UserEntityDetail(null))->toArray($this->request);
        }

        return $this->asJson($entity, 'detail', $checkPrivacy);
    }

    /**
     * @param mixed $entity
     * @param mixed $checkPrivacy
     * @return array
     * @throws AuthenticationException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function transactionUser(mixed $entity, mixed $checkPrivacy = false): array
    {
        if (!$entity instanceof Model || (Arr::has($entity->getAttributes(), 'deleted_at') && null !== $entity->deleted_at)) {
            return [
                'full_name'    => __p('core::phrase.deleted_user'),
                'display_name' => __p('core::phrase.deleted_user'),
            ];
        }

        return $this->asJson($entity, 'detail', $checkPrivacy);
    }

    /**
     * @param  mixed                                                             $entity
     * @param  bool                                                              $checkPrivacy
     * @return array|null
     * @throws AuthenticationException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function embed(mixed $entity, mixed $checkPrivacy = 'view'): ?array
    {
        if (!$entity) {
            return null;
        }

        return $this->asJson($entity, 'embed', $checkPrivacy);
    }

    /**
     * @param  mixed                                                             $entity
     * @param  bool                                                              $checkPrivacy
     * @return array|null
     * @throws AuthenticationException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function item(mixed $entity, mixed $checkPrivacy = 'view'): ?array
    {
        if (!$entity) {
            return null;
        }

        return $this->asJson($entity, 'item', $checkPrivacy);
    }

    /**
     * @param  \Illuminate\Support\Collection $items
     * @param  mixed                          $checkPrivacy
     * @return array|null
     */
    public function items(\Illuminate\Support\Collection $items, mixed $checkPrivacy = 'view'): ?array
    {
        if ($items->isEmpty()) {
            return [];
        }

        return $items->map(fn ($item) => $this->item($item, $checkPrivacy))
            ->reject(fn ($item) => null == $item)->toArray();
    }
    /**
     * @param  \Illuminate\Support\Collection $items
     * @param  mixed                          $checkPrivacy
     * @return array|null
     */
    public function embeds(\Illuminate\Support\Collection $items, mixed $checkPrivacy = 'view'): ?array
    {
        if ($items->isEmpty()) {
            return [];
        }

        return $items->map(fn ($item) => $this->embed($item, $checkPrivacy))
            ->reject(fn ($item) => null == $item)->toArray();
    }

    public function getItem(mixed $alias, mixed $id): mixed
    {
        /** @var Model $model */
        $model = Relation::getMorphedModel($alias);

        return $model ? $model::find($id) : null;
    }

    public function toItem(mixed $alias, mixed $id, mixed $checkPrivacy = 'view'): ?JsonResource
    {
        /** @var Model $model */
        $model = Relation::getMorphedModel($alias);

        $item = $model::find($id);

        return $item ? $this->asResource($item, 'item', $checkPrivacy) : null;
    }

    public function toResource(mixed $variant, mixed $alias, mixed $id, mixed $checkPrivacy = 'view'): ?JsonResource
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = Relation::getMorphedModel($alias);

        $item = $model::find($id);

        return $item ? $this->asResource($item, $variant, $checkPrivacy) : null;
    }

    /**
     * @return ResourceCollection<JsonResource>|null
     */
    public function toCollection(): ?ResourceCollection
    {
        return null;
    }
}
