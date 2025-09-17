<?php

namespace MetaFox\Platform\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Support\Browse\Scopes\Phrase\TranslatableTextSearchScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\Contracts\CategoryRepositoryInterface;

/**
 * Trait HasApprove.
 *
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
abstract class AbstractCategoryRepository extends AbstractRepository implements CategoryRepositoryInterface
{
    public function createCategory(User $context, array $attributes): Model
    {
        $attributes['is_active'] = Arr::get($attributes, 'is_active', 0);

        $parentId = Arr::get($attributes, 'parent_id');

        $attributes['level'] = 1;

        if ($parentId) {
            $parent = $this->find($parentId);

            $attributes['level'] += $parent->level;
        }

        if ($attributes['level'] > MetaFoxConstant::MAX_CATEGORY_LEVEL) {
            abort(403, json_encode([
                'title'   => __p('core::phrase.content_is_not_available'),
                'message' => __p('core::phrase.it_is_not_allowed_to_move_this_category_and_subcategories_to_the_selected_category', [
                    'maxValue' => MetaFoxConstant::MAX_CATEGORY_LEVEL,
                ]),
            ]));
        }

        $attributes['ordering'] = $this->getNextOrdering($attributes['level']);

        $category = $this->getModel()->newQuery()->create($attributes);

        $category->refresh();
        $categoryIds = array_merge([$category->entityId()], $category->subCategories()->pluck('id')->toArray());

        $this->createCategoryRelationFor(category: $category, categoryIds: $categoryIds);
        $this->clearCache();

        return $category;
    }

    protected function getNextOrdering(int $level): int
    {
        $currentCategory = $this->getModel()->newQuery()
            ->where([
                'level' => $level,
            ])
            ->orderByDesc('ordering')
            ->first();

        if (null === $currentCategory) {
            return 0;
        }

        return (int) $currentCategory->ordering + 1;
    }

    public function updateCategory(User $context, int $id, array $attributes): Model
    {
        $category    = $this->find($id);
        $oldParentId = $category->parent_id;
        $newParentId = Arr::get($attributes, 'parent_id');

        if ($newParentId !== null && $newParentId !== $oldParentId) {
            $newCategory         = $this->find($newParentId);
            $attributes['level'] = $newCategory->level + 1;

            $this->changeParentCategory($category, $newParentId);
        }

        if ($newParentId == null) {
            $this->decrementTotalItemCategories($category?->parentCategory, $category->total_item);
            $attributes['level'] = 1;
        }

        $category->fill($attributes)->save();
        $category->refresh();
        $this->clearCache();

        return $category;
    }

    public function deleteOrMoveToNewCategory(Model $category, int $newCategoryId): bool
    {
        if ($newCategoryId > 0) {
            if (method_exists($this, 'moveToNewCategory')) {
                $this->moveToNewCategory($category, $newCategoryId, true);
            }

            $this->clearCache();

            return (bool) $category->forceDelete();
        }

        if (method_exists($this, 'deleteAllBelongTo')) {
            $this->deleteAllBelongTo($category);
        }

        $this->deleteCategoryRelations(category: $category);
        $this->clearCache();

        return (bool) $category->forceDelete();
    }

    public function getCategoriesForForm(): array
    {
        $categoryIds = $this->fetchSortedCategories($this->getActiveCategoryIds());

        return $this->setActiveStatusForForm($categoryIds);
    }

    public function getCategoriesForStoreForm(?Model $category): array
    {
        $maxLevel = MetaFoxConstant::MAX_CATEGORY_LEVEL - 1;

        $query = $this->getModel()->newQuery()
            ->where('is_active', MetaFoxConstant::IS_ACTIVE);

        if (null !== $category) {
            $maxLevel = $this->getMaxDepth($category);

            if (is_numeric($maxLevel) && ($relationModel = $this->relationModel()) instanceof Model) {
                $maxLevel = max(0, MetaFoxConstant::MAX_CATEGORY_LEVEL - $maxLevel);

                $query->whereNotIn('id', $relationModel::query()->where('parent_id', '=', $category->entityId())->select('child_id'));
            } else {
                $query->where('id', '<>', $category->entityId());

                $maxLevel = $category->level;

                if ($category->subCategories()->exists()) {
                    $maxLevel = max(0, $category->level - 1);
                }

                if ($category->subCategories()->exists() && !$category->parentCategory()->exists()) {
                    return [];
                }
            }
        }

        if (0 === $maxLevel) {
            return [];
        }

        return $query->select('id as value', 'name as label', 'parent_id', 'is_active', 'level')
            ->where('level', '<=', $maxLevel)
            ->orderBy('ordering')
            ->get()
            ->toArray();
    }

    public function getCategoryForFilter(): Collection
    {
        $idsInactive = $this->getCategoryInactive();
        $table       = $this->getModel()->getTable();

        $query = $this->getModel()->newQuery()
            ->where('is_active', MetaFoxConstant::IS_ACTIVE)
            ->where(function (Builder $builder) use ($idsInactive, $table) {
                $builder->whereNotIn("$table.id", array_unique($idsInactive));
            });

        $query->where("$table.level", 1);

        return $query->with($this->getRelation($table))
            ->orderBy("$table.ordering")
            ->get(["$table.*"])
            ->collect();
    }

    public function getStructure(User $context, array $attributes): array
    {
        if (empty(Arr::except($attributes, ['limit']))) {
            return localCacheStore()
                ->rememberForever(
                    $this->getStructureCacheKey(),
                    fn () => ResourceGate::items($this->getAllCategories($context, []))
                );
        }

        return ResourceGate::items($this->getAllCategories($context, $attributes));
    }

    public function getAllCategories(User $context, array $attributes): Collection
    {
        $idsInactive = $this->getCategoryInactive();
        $search      = Arr::get($attributes, 'q');
        $level       = Arr::get($attributes, 'level', 1);
        $table       = $this->getModel()->getTable();

        $query = $this->getModel()->newQuery()
            ->where('is_active', MetaFoxConstant::IS_ACTIVE)
            ->where(function (Builder $builder) use ($idsInactive, $table) {
                $builder->whereNotIn("$table.id", array_unique($idsInactive));
            });

        if ($search !== null) {
            $defaultLocale = Language::getDefaultLocaleId();
            $searchScope   = new TranslatableTextSearchScope($search, ['ps.text']);
            $searchScope->setLocale($defaultLocale);

            return $query->addScope($searchScope)->get(["$table.*"])->collect();
        }

        if (array_key_exists('id', $attributes)) {
            return $query->where("$table.id", '=', $attributes['id'])->get()->collect();
        }

        $key = $this->getViewAllCacheId();

        if ($level != 0) {
            $key = $this->getViewLevelCacheId();
            $query->where("$table.level", $level);
        }

        return Cache::rememberForever($key, function () use ($query, $table) {
            return $query->with($this->getRelation($table))
                ->orderBy("$table.ordering")
                ->get(["$table.*"])
                ->collect();
        });
    }

    protected function getRelation(string $table): array
    {
        return [
            'subCategories' => function (HasMany $q) use ($table) {
                $q->where("$table.is_active", MetaFoxConstant::IS_ACTIVE)
                    ->with($this->getRelation($table))
                    ->orderBy("$table.ordering");
            },
        ];
    }

    protected function getCategoryInactive()
    {
        /** @var \Illuminate\Database\Eloquent\Collection $categories */
        $categories = $this->getModel()->newQuery()
            ->select(['id', 'level', 'parent_id', 'is_active'])
            ->orderByDesc('level')->get();

        $depth = $categories->first()?->level;

        if (!$depth) {
            return [];
        }

        return Cache::rememberForever($this->getViewInactiveCacheId(), function () use ($categories, $depth) {
            $idsInactive = [];

            for ($level = 1; $level <= $depth; $level++) {
                $idsInactive = array_merge($categories->filter(function ($item) use ($level, $idsInactive) {
                    return ($item->is_active == MetaFoxConstant::IS_INACTIVE && $item->level == $level)
                        || in_array($item->parent_id, $idsInactive);
                })->pluck('id')->toArray(), $idsInactive);
            }

            return array_unique($idsInactive);
        });
    }

    public function viewForAdmin(User $context, array $attributes)
    {
        $parentId = Arr::get($attributes, 'parent_id');
        $search   = Arr::get($attributes, 'q');
        $table    = $this->getModel()->getTable();
        $query    = $this->getModel()::query()
            ->orderBy("$table.ordering")
            ->select("$table.*")
            ->with(['subCategories', 'parentCategory']);

        if (null === $parentId) {
            $query->whereNull('parent_id');
        }

        if (is_numeric($parentId)) {
            $query->where('parent_id', '=', $parentId);
        }

        if ($search) {
            $defaultLocale = Language::getDefaultLocaleId();

            $query->leftJoin('phrases as ps', function (JoinClause $join) use ($table) {
                $join->on('ps.key', '=', "$table.name");
            });

            $query->where(function (Builder $builder) use ($table, $search, $defaultLocale) {
                $builder->where(DB::raw("CASE when ps.name is null then $table.name else ps.text end"), $this->likeOperator(), '%' . $search . '%');
                $builder->whereRaw("CASE when ps.name is null then ps.locale is null else ps.locale = '$defaultLocale' end");
            });
        }

        return $query->get();
    }

    public function clearCache()
    {
        Cache::forget($this->getCategoriesCacheId());
        Cache::forget($this->getActiveCategoryIdsCacheId());
        Cache::forget($this->getAllRelationsCacheId());
        Cache::forget($this->getCategoriesActiveOnlyCacheId());
        Cache::forget($this->getViewAllCacheId());
        Cache::forget($this->getViewLevelCacheId());
        Cache::forget($this->getViewInactiveCacheId());
        localCacheStore()->forget($this->getStructureCacheKey());
    }

    /**
     * @inheritDoc
     */
    public function incrementTotalItemCategories(?Model $category, int $totalItem): void
    {
        if (!$category instanceof Model) {
            return;
        }

        do {
            $total = $totalItem + $category->total_item;
            $category->update(['total_item' => $total]);
            $category = $category?->parentCategory;
        } while ($category);
    }

    /**
     * @inheritDoc
     */
    public function decrementTotalItemCategories(?Model $category, int $totalItem): void
    {
        if (!$category instanceof Model) {
            return;
        }

        do {
            $total = $category->total_item - $totalItem;
            $category->update(['total_item' => $total]);
            $category = $category?->parentCategory;
        } while ($category);
    }

    public function orderCategories(array $orderIds): bool
    {
        $categories = $this->getModel()->newQuery()
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        if (!$categories->count()) {
            return true;
        }

        $ordering = 1;

        foreach ($orderIds as $orderId) {
            $category = $categories->get($orderId);

            if (!is_object($category)) {
                continue;
            }

            $category->update(['ordering' => $ordering++]);
        }

        $this->clearCache();

        return true;
    }

    public function toggleActive(int $id): Model
    {
        $item = $this->find($id);

        if (!$item instanceof Model) {
            abort(403, __p('core::validation.category_id.exists'));
        }

        if ($item->is_default) {
            abort(403, __p('core::validation.category_id.default'));
        }

        $item->update(['is_active' => $item->is_active ? 0 : 1]);

        $this->clearCache();

        return $item;
    }

    protected function getCategoriesCacheId(): string
    {
        $translationView = config('localize.view_mode', 'view');

        return $this->getModel()->getMorphClass() . '_getCategories' . "_$translationView";
    }

    protected function getCategoriesActiveOnlyCacheId(): string
    {
        $translationView = config('localize.view_mode', 'view');

        return $this->getModel()->getMorphClass() . '_getCategoriesActiveOnly' . "_$translationView";
    }

    protected function getViewAllCacheId(): string
    {
        $translationView = config('localize.view_mode', 'edit');

        return $this->getModel()->getMorphClass() . '_get_all' . "_$translationView";
    }

    protected function getViewLevelCacheId(): string
    {
        $translationView = config('localize.view_mode', 'view');

        return $this->getModel()->getMorphClass() . '_level' . "_$translationView";
    }

    protected function getAllRelationsCacheId(): string
    {
        return $this->getModel()->getMorphClass() . '_getAllRelations';
    }

    protected function getActiveCategoryIdsCacheId(): string
    {
        $translationView = config('localize.view_mode', 'view');

        return $this->getModel()->getMorphClass() . '_getActiveCategoryIds' . "_$translationView";
    }

    protected function getViewInactiveCacheId(): string
    {
        $translationView = config('localize.view_mode', 'view');

        return $this->getModel()->getMorphClass() . '_inactive' . "_$translationView";
    }

    protected function getDefaultCategoryParentIdsCacheKey(): string
    {
        $translationView = config('localize.view_mode', 'view');

        return $this->getModel()->getMorphClass() . '_default_parent_ids' . "_$translationView";
    }

    protected function getStructureCacheKey(): string
    {
        $translationView = config('localize.view_mode', 'view');

        return get_called_class() . '::getStructure' . "_$translationView";
    }

    protected function getCategoryRelation(?array $categoryIds): Builder
    {
        $table = $this->getModel()->getTable();

        $subQuery = $this->getModel()->newModelQuery()
            ->select(
                "$table.id as sub_id",
                'child.parent_id as child_parent_id',
                DB::raw("(case when child.id is null then $table.id
                                   else child.id end) as child_id"),
                DB::raw('(case when child.level is null then 0
                                   else child.level end) as child_level')
            )
            ->leftJoin("$table as child", 'child.id', '=', "$table.parent_id");

        if (is_array($categoryIds) && count($categoryIds) > 0) {
            $subQuery->whereIn("$table.id", $categoryIds);
        }

        return $this->getModel()->newQuery()
            ->select(
                "$table.id as parent_id",
                's.sub_id as child_id',
                DB::raw("(case
                                   when $table.parent_id is null then s.child_level + $table.level
                                   when s.sub_id = $table.id then 1
                                   else s.child_level end) as depth")
            )
            ->joinSub(
                $subQuery,
                's',
                function (JoinClause $joinClause) use ($table) {
                    $joinClause->on('s.child_id', '=', "$table.id");
                    $joinClause->orWhere('s.child_parent_id', '=', DB::raw("$table.id"));
                    $joinClause->orWhere('s.sub_id', '=', DB::raw("$table.id"));
                }
            );
    }

    /**
     * @param Model $category
     * @param int   $newCategoryId
     *
     * @return void
     */
    protected function changeParentCategory(Model $category, int $newCategoryId): void
    {
        $totalItem = $category->total_item;
        $parent    = $category?->parentCategory;
        $this->decrementTotalItemCategories($parent, $totalItem);
        $newCategory = $this->find($newCategoryId);
        $categoryIds = array_merge([$category->entityId()], $category->subCategories()->pluck('id')->toArray());

        if (is_numeric($maxDepth = $this->getMaxDepth($category))) {
            $maxLevel = max(0, MetaFoxConstant::MAX_CATEGORY_LEVEL - $maxDepth);

            $invalid = $newCategory->level > $maxLevel;
        } else {
            $invalid = $newCategory->level >= $category->level && $category->subCategories()->exists();
        }

        if ($invalid) {
            abort(403, json_encode([
                'title'   => __p('core::phrase.content_is_not_available'),
                'message' => __p('core::phrase.it_is_not_allowed_to_move_this_category_and_subcategories_to_the_selected_category', [
                    'maxValue' => MetaFoxConstant::MAX_CATEGORY_LEVEL,
                ]),
            ]));
        }

        $category->update([
            'parent_id' => $newCategory->entityId(),
            'level'     => $newCategory->level + 1,
        ]);

        $category->refresh();

        //update new level for category children
        $this->getModel()->newQuery()
            ->where('parent_id', $category->entityId())
            ->update(['level' => $category->level + 1]);

        $this->deleteCategoryRelations(category: $category);

        $this->createCategoryRelationFor(category: $category, categoryIds: $categoryIds);
        $this->clearCache();

        $this->incrementTotalItemCategories($newCategory, $totalItem);
    }

    public function getCategories(bool $activeOnly = true): Collection
    {
        $cacheKey = $activeOnly
            ? $this->getCategoriesActiveOnlyCacheId()
            : $this->getCategoriesCacheId();

        return Cache::rememberForever($cacheKey, function () use ($activeOnly) {
            $query = $this->getModel()->newQuery()
                ->select('id as value', 'name as label', 'parent_id', 'is_active', 'level', 'ordering')
                ->orderBy('ordering');

            if ($activeOnly) {
                $query->where('is_active', 1);
            }

            return $query->get();
        });
    }

    /**
     * @param Model $category
     * @param array $categoryIds
     *
     * @return void
     */
    public function createCategoryRelationFor(Model $category, array $categoryIds = []): void
    {
        $model = $this->relationModel();
        if (!$model instanceof Model) {
            return;
        }

        if (empty($categoryIds)) {
            $categoryIds = array_merge([$category->entityId()], $category->subCategories()->pluck('id')->toArray());
        }

        $query = $this->getCategoryRelation($categoryIds);

        $model->newQuery()->insertUsing(['parent_id', 'child_id', 'depth'], $query);
    }

    /**
     * @return void
     */
    public function createCategoryRelation(): void
    {
        $categoryIds = null;
        $model       = $this->relationModel();
        if (!$model instanceof Model) {
            return;
        }

        $query = $this->getCategoryRelation($categoryIds);

        $model->newQuery()->insertUsing(['parent_id', 'child_id', 'depth'], $query);
    }

    /**
     * @param Model $category
     *
     * @return void
     */
    public function deleteCategoryRelations(Model $category): void
    {
        $categoryId = $category->entityId();

        $model = $this->relationModel();

        if (!$model instanceof Model) {
            return;
        }

        $model->newQuery()
            ->where('child_id', $categoryId)
            ->orWhere('parent_id', $categoryId)
            ->delete();
    }

    /**
     * @param int $categoryId
     *
     * @return array
     */
    public function getChildrenIds(int $categoryId): array
    {
        $model = $this->relationModel();

        if (!$model instanceof Model) {
            return [];
        }

        return $model->newQuery()->where('parent_id', $categoryId)
            ->pluck('child_id')->toArray();
    }

    public function getCategoriesForUpdateForm(Collection $selectedCategories): array
    {
        $categoryIds = $this->fetchSortedCategories(array_merge(
            $this->getActiveCategoryIds(),
            $selectedCategories->pluck('id')->toArray(),
            $this->getParentSelectedIds($selectedCategories)
        ));

        return $this->setActiveStatusForForm($categoryIds);
    }

    public function getActiveCategoryIds(): array
    {
        return Cache::rememberForever($this->getActiveCategoryIdsCacheId(), function () {
            return $this->collectActiveIds();
        });
    }

    protected function setActiveStatusForForm(array $options): array
    {
        $activeCategoryIds = $this->getActiveCategoryIds();

        return array_map(function ($option) use ($activeCategoryIds) {
            $option['is_active'] = (int) in_array($option['value'], $activeCategoryIds);

            return $option;
        }, $options);
    }

    protected function collectActiveIds(array $parentIds = [null]): array
    {
        $result = array_filter($parentIds);

        foreach ($parentIds as $parentId) {
            $childrenIds = $this->getCategories()
                ->where('parent_id', '=', $parentId)
                ->pluck('value')
                ->toArray();

            if (empty($childrenIds)) {
                continue;
            }

            $result = array_merge($result, $this->collectActiveIds($childrenIds));
        }

        return $result;
    }

    protected function fetchSortedCategories(array $categoryIds): array
    {
        return $this->getCategories(false)
            ->whereIn('value', $categoryIds)
            ->sortBy('ordering')
            ->values()
            ->toArray();
    }

    protected function getParentSelectedIds(Collection $categories): array
    {
        return $categories
            ->filter(fn ($item) => $item->parent_id !== null)
            ->flatMap(function ($category) {
                return $this->getAllRelations()
                    ->where('child_id', $category->id)
                    ->pluck('parent_id')->toArray();
            })
            ->unique()
            ->toArray();
    }

    protected function getAllRelations(): Collection
    {
        $model = $this->relationModel();

        if (!$model instanceof Model) {
            return collect([]);
        }

        return Cache::rememberForever($this->getAllRelationsCacheId(), function () use ($model) {
            return $model::query()->get(['child_id', 'parent_id', 'depth']);
        });
    }

    /**
     * @param int $categoryId
     *
     * @return array
     */
    public function getParentIds(int $categoryId): array
    {
        $model = $this->relationModel();

        if (!$model instanceof Model) {
            return [];
        }

        return $model->newQuery()->where('child_id', $categoryId)
            ->whereNot('parent_id', $categoryId)
            ->pluck('parent_id')->toArray();
    }

    /**
     * @inheritDoc
     */
    public function createTopLevelCategoryRelation(): void
    {
        $model = $this->relationModel();

        if (!$model instanceof Model) {
            return;
        }

        $query = $this->getModel()->newQuery()
            ->select('id as parent_id', 'id as child_id', 'level')
            ->whereNull('parent_id');

        $model->newQuery()->insertUsing(['parent_id', 'child_id', 'depth'], $query);
    }

    public function migrateCategoryRelationAfterImport(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }
        $model = $this->relationModel();

        if (!$model instanceof Model) {
            return;
        }

        $model::truncate();
        $this->createCategoryRelation();
    }

    public function migrateTopLevelCategoryRelation(): void
    {
        $relationModel = $this->relationModel();

        if (!$relationModel instanceof Model) {
            return;
        }

        $topLevelCategoryIds = $this->getModel()::query()
            ->whereNull('parent_id')
            ->pluck('id')
            ->toArray();

        if (empty($topLevelCategoryIds)) {
            return;
        }

        $relationModel::query()
            ->whereIn('parent_id', $topLevelCategoryIds)
            ->whereIn('child_id', $topLevelCategoryIds)
            ->where('depth', 1)
            ->delete();

        $newRelations = array_map(
            fn ($categoryId) => ['parent_id' => $categoryId, 'child_id' => $categoryId, 'depth' => 1],
            $topLevelCategoryIds
        );

        $relationModel::query()->insert($newRelations);
    }

    public function hasLinkedItem(int $categoryId, int $itemId): bool
    {
        $model = $this->categoryDataModel();

        if (!$model instanceof Model) {
            return false;
        }

        return $model->newQuery()->where([
            'category_id' => $categoryId,
            'item_id'     => $itemId,
        ])->exists();
    }


    /**
     * @param Model $category
     *
     * @return int
     */
    public function isActive(Model $category): int
    {
        if (!$category->is_active) {
            return 0;
        }

        $parentIds = $this->getParentIds($category->entityId());
        $activeIds = $this->getActiveCategoryIds();

        if (!$category->parentCategory instanceof Model) {
            return $category->is_active;
        }

        $parentIds = array_filter($parentIds, function ($id) use ($activeIds) {
            return !in_array($id, $activeIds);
        });

        return (int) empty($parentIds);
    }

    protected function relationModel(): ?Model
    {
        return method_exists($this, 'getRelationModel') ? $this->getRelationModel() : null;
    }

    protected function categoryDataModel(): ?Model
    {
        return method_exists($this, 'getCategoryDataModel') ? $this->getCategoryDataModel() : null;
    }

    protected function getMaxDepth(Model $category): ?int
    {
        if (!($relatedModel = $this->relationModel()) instanceof Model) {
            return null;
        }

        try {
            return $relatedModel::query()
                ->where('parent_id', '=', $category->entityId())
                ->max('depth');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    public function getParentOptionsForDeleteWithMigration(Model $category): array
    {
        if (null === ($maxDepth = $this->getMaxDepth($category))) {
            $query = $this->getCategoriesForForm();

            $query = collect($query)
                ->where('value', '<>', $category->entityId())
                ->where('level', '<=', $category->level);

            if (count($category?->subCategories) > 0) {
                $query->where('level', '<', MetaFoxConstant::MAX_CATEGORY_LEVEL);
            }

            return $query->toArray();
        }

        $maxLevel = max(0, MetaFoxConstant::MAX_CATEGORY_LEVEL - ($maxDepth - 1));

        if (0 === $maxLevel) {
            return [];
        }

        $relationModel = $this->relationModel();

        return $this->getModel()->newQuery()
            ->select('id as value', 'name as label', 'parent_id', 'is_active', 'level')
            ->where('is_active', MetaFoxConstant::IS_ACTIVE)
            ->whereNotIn('id', $relationModel::query()->where('parent_id', '=', $category->entityId())->select('child_id'))
            ->where('level', '<=', $maxLevel)
            ->orderBy('ordering')
            ->get()
            ->toArray();
    }
}
