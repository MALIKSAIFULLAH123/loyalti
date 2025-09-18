<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Group\Jobs\DeleteGroupTypeJob;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Type;
use MetaFox\Group\Policies\TypePolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class TypeRepository.
 * @method Type getModel()
 * @method Type find($id, $columns = ['*'])
 * @ignore
 */
class TypeRepository extends AbstractRepository
{
    public function model(): string
    {
        return Type::class;
    }

    public function createGroupType(User $context, array $attributes): Type
    {
        policy_authorize(TypePolicy::class, 'create', $context);

        $storageFile = upload()->setPath('group_type')
            ->storeFile($attributes['image']);
        unset($attributes['image']);

        $attributes['image_server_id'] = $storageFile->storage_id;
        $attributes['image_path'] = $storageFile->path;
        $attributes['image_file_id'] = $storageFile->id;

        /** @var Type $type */
        $type = parent::create($attributes);
        $type->refresh();

        return $type;
    }

    public function updateGroupType(User $context, int $id, array $attributes): Type
    {
        policy_authorize(TypePolicy::class, 'update', $context);

        $type = $this->find($id);

        if (isset($attributes['image'])) {
            $storageFile = upload()->setPath('group_type')->storeFile($attributes['image']);
            unset($attributes['image']);

            $attributes['image_server_id'] = $storageFile->storage_id;
            $attributes['image_path'] = $storageFile->path;
            $attributes['image_file_id'] = $storageFile->id;
        }

        $type->update($attributes);
        $type->refresh();

        return $type;
    }

    public function viewGroupType(User $context, int $id): Type
    {
        policy_authorize(TypePolicy::class, 'view', $context);

        return $this->find($id);
    }

    public function viewGroupTypes(User $context, array $attributes): Paginator
    {
        policy_authorize(TypePolicy::class, 'viewAny', $context);
        $limit = $attributes['limit'];

        return $this->getModel()->newModelQuery()
            ->with('categories')
            ->where('is_active', Type::IS_ACTIVE)
            ->whereHas('categories', function (Builder $query) {
                $query->where('is_active', Category::IS_ACTIVE);
            })
            ->orderBy('ordering')
            ->orderBy('id')
            ->simplePaginate($limit);
    }

    public function getGroupTypeForForm(User $context): array
    {
        policy_authorize(TypePolicy::class, 'viewAny', $context);

        $types = $this->getModel()->newModelQuery()
            ->with([
                'categories' => function (HasMany $q) {
                    $q->where('is_active', Category::IS_ACTIVE)
                        ->orderBy('ordering')
                        ->orderBy('id');
                },
            ])
            ->where('is_active', Type::IS_ACTIVE)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get(['id', 'name']);

        $typeData = [];
        foreach ($types as $groupType) {
            /** @var Type $groupType */
            $categories = $groupType->categories->map(function (Category $category) {
                return [
                    'id'   => $category->entityId(),
                    'name' => $category->name,
                ];
            })->toArray();

            if (empty($categories)) {
                continue;
            }

            $typeData[$groupType->entityId()] = [
                'id'         => $groupType->entityId(),
                'name'       => $groupType->name,
                'categories' => $categories,
            ];
        }

        return $typeData;
    }

    public function viewGroupTypesForAdmin(User $context, array $attributes): Paginator
    {
        policy_authorize(TypePolicy::class, 'viewAny', $context);
        $limit = $attributes['limit'];

        return $this->getModel()->newQuery()
            ->orderBy('ordering')
            ->orderBy('id')
            ->simplePaginate($limit);
    }

    public function deleteGroupType(User $context, int $id, int $newTypeId): bool
    {
        policy_authorize(TypePolicy::class, 'delete', $context);

        $type = $this->find($id);

        DeleteGroupTypeJob::dispatch($type, $newTypeId);

        return true;
    }

    public function moveToNewType(Type $type, int $newTypeId): bool
    {
        $newType = $this->find($newTypeId);

        //Move page
        Group::query()->where('type_id', '=', $type->entityId())
            ->update(['type_id' => $newType->entityId()]);

        //Move category
        Category::query()->where('type_id', '=', $type->entityId())
            ->update(['type_id' => $newType->entityId()]);

        return true;
    }

    public function deleteAllBelongTo(Type $type): bool
    {
        $type->groups()->each(function (Group $page) {
            $page->delete();
        });

        $type->categories()->each(function (Category $category) {
            $category->delete();
        });

        return true;
    }

    public function deleteOrMove(Type $category, int $newTypeId): bool
    {
        if ($newTypeId) {
            $this->moveToNewType($category, $newTypeId);

            return (bool)$category->delete();
        }

        $this->deleteAllBelongTo($category);

        return (bool)$category->delete();
    }

    /**
     * @inheritDoc
     */
    public function getGroupTypeForFilter(): Collection
    {
        return $this->getModel()->newModelQuery()
            ->with([
                'categories' => function (HasMany $q) {
                    $q->where('is_active', Category::IS_ACTIVE)
                        ->orderBy('ordering')
                        ->orderBy('id');
                },
            ])
            ->where('is_active', Type::IS_ACTIVE)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get()
            ->collect();
    }

    /**
     * @inheritDoc
     */
    public function getGroupTypeOptions(): array
    {
        $options = $subOptions = [];

        $types = $this->getModel()->newModelQuery()
            ->with([
                'categories' => function (HasMany $q) {
                    $q->where('is_active', Category::IS_ACTIVE)
                        ->orderBy('ordering')
                        ->orderBy('id');
                },
            ])
            ->where('is_active', Type::IS_ACTIVE)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get()
            ->collect();

        $types->each(function (Type $type) use (&$options, &$subOptions) {
            $subOptions[$type->entityId()] = collect($type->categories)->map(function (Category $category) {
                return [
                    'label' => $category->name,
                    'value' => $category->entityId(),
                ];
            })->values()->toArray();
            $options[] = [
                'label' => $type->name,
                'value' => $type->entityId(),
            ];

            return true;
        });

        return [$options, $subOptions];
    }
}
