<?php

namespace MetaFox\Activity\Repositories\Eloquent;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Type;
use MetaFox\Activity\Policies\TypePolicy;
use MetaFox\Activity\Repositories\TypeRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\JoinClause;

class TypeRepository extends AbstractRepository implements TypeRepositoryInterface
{
    public function model()
    {
        return Type::class;
    }

    public function updateType(User $context, int $id, array $attributes): Type
    {
        /** @var Type $resource */
        $resource = $this->find($id);

        policy_authorize(TypePolicy::class, 'update', $context, $resource);

        $fields = $this->getModel()->getFillable();

        $data = Arr::only($attributes, $fields);

        $newPermissions = Arr::except($attributes, $fields) ?? [];

        if (is_array($newPermissions)) {
            $newPermissions = Arr::map($newPermissions, function ($permission) {
                return (bool) $permission;
            });
        }

        $defautlValues        = is_array($resource->value_default) ? $resource->value_default : [];
        $actualValues         = is_array($resource->value_actual) ? $resource->value_actual : [];
        $oldPermissions       = !empty($actualValues) ? $actualValues : $defautlValues;
        $data['value_actual'] = array_merge($oldPermissions, $newPermissions);

        $resource->fill($data);

        $resource->save();

        $activityTypeManager = resolve(TypeManager::class);

        $activityTypeManager->refresh();

        $activityTypeManager->cleanData();

        return $resource;
    }

    public function deleteType(User $context, int $id): int
    {
        $resource = $this->find($id);

        policy_authorize(TypePolicy::class, 'delete', $context, $resource);

        $response = $this->delete($id);

        $activityTypeManager = resolve(TypeManager::class);

        $activityTypeManager->refresh();

        $activityTypeManager->cleanData();

        return $response;
    }

    public function getTypeByType(string $type): ?Type
    {
        return $this->getModel()->newQuery()->where('type', $type)->first();
    }

    public function getActiveTypeValues(): array
    {
        return $this->getActiveTypes()->pluck('type')->toArray();
    }

    public function getActiveTypeOptions(): array
    {
        return $this->getActiveTypes()->map(function (Type $type) {
            return ['label' => $type->title, 'value' => $type->type];
        })->toArray();
    }

    public function getActiveEntityTypeOptions(): array
    {
        return $this->getActiveTypes()
            ->unique('entity_type')
            ->map(function (Type $type) {
                return [
                    'label' => Str::headline(__p_type_key($type->entity_type)),
                    'value' => $type->entity_type,
                ];
            })->toArray();
    }

    private function getActiveTypes(): Collection
    {
        return localCacheStore()->rememberForever(
            __METHOD__,
            function () {
                return $this->getModel()
                    ->newQuery()
                    ->select(['title', 'type', 'entity_type'])
                    ->where('is_active', 1)
                    ->addScope(new PackageScope($this->getModel()->getTable()))
                    ->get();
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function viewTypes(User $context, array $attributes = []): Paginator
    {
        $q             = Arr::get($attributes, 'q');
        $limit         = Arr::get($attributes, 'limit');
        $moduleId      = Arr::get($attributes, 'module_id');
        $active        = Arr::get($attributes, 'is_active');
        $canCreateFeed = Arr::get($attributes, 'can_create_feed');

        $table = $this->getModel()->getTable();
        $query = $this->getModel()->newQuery();

        if ($q) {
            $query->addScope(new SearchScope($q, ['type', 'title']));
        }

        if ($moduleId) {
            $query->where("$table.module_id", '=', $moduleId);
        }

        if (null !== $active) {
            $query->where("$table.is_active", $active ? 1 : 0);
        }

        if (null !== $canCreateFeed) {
            $query->where(function (Builder $subQuery) use ($table, $canCreateFeed) {
                $likePattern = '"can_create_feed":' . ($canCreateFeed ? 'true' : 'false');

                $subQuery->where("$table.value_actual", 'like', "%$likePattern%")
                    ->orWhere(function (Builder $sub) use ($likePattern, $table) {
                        $sub->whereNull("$table.value_actual")->where("$table.value_default", 'like', "%$likePattern%");
                    });
            });
        }

        $packageScope = new PackageScope($table);
        $query->addScope($packageScope);

        $query->join('packages', function (JoinClause $subQuery) use ($table) {
            $subQuery->on('packages.alias', '=', "$table.module_id");
        });

        return
            $query
            ->select(["$table.*", 'packages.title as package_title'])
            ->with(['package'])
            ->orderBy('package_title')
            ->orderBy("$table.title")
            ->paginate($limit);
    }
}
