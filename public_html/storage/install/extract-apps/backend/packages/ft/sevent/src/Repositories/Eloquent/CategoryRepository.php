<?php

namespace Foxexpert\Sevent\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use Foxexpert\Sevent\Jobs\DeleteCategoryJob;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Models\Category;
use Foxexpert\Sevent\Models\CategoryRelation;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractCategoryRepository;

/**
 * Class SeventCategoryRepository.
 * @property Category $model
 * @method   Category getModel()
 * @method   Category find($id, $columns = ['*'])()
 * @ignore
 * @codeCoverageIgnore
 */
class CategoryRepository extends AbstractCategoryRepository implements CategoryRepositoryInterface
{
    public function model(): string
    {
        return Category::class;
    }

    public function viewCategory(User $context, int $id): Category
    {
        $relation = [
            'subCategories' => function (HasMany $query) {
                $query->where('is_active', Category::IS_ACTIVE);
            },
        ];

        return $this->with($relation)->find($id);
    }

    public function deleteCategory(User $context, int $id, int $newCategoryId): bool
    {
        $category = $this->find($id);

        $category->delete();

        DeleteCategoryJob::dispatch($category, $newCategoryId);
        $this->clearCache();

        return true;
    }

    public function moveToNewCategory(Category $category, int $newCategoryId, bool $isDelete = false): void
    {
        $totalItem = $category->total_item;
        $parent    = $category?->parentCategory;
        $this->decrementTotalItemCategories($parent, $totalItem);

        $newCategory = $this->find($newCategoryId);
        $seventIds     = $category->sevents()->pluck('sevents.id')->toArray();

        //Move sevent
        if (!empty($seventIds) && $isDelete) {
            $newCategory->sevents()->sync($seventIds, false);
        }

        //update parent_id
        Category::query()->where('parent_id', '=', $category->entityId())->update([
            'parent_id' => $newCategory->entityId(),
            'level'     => $newCategory->level + 1,
        ]);

        $this->deleteCategoryRelations($category);

        $this->createCategoryRelationFor($category);
    }

    public function deleteAllBelongTo(Category $category): bool
    {
        $category->sevents()->each(function (Sevent $sevent) {
            $sevent->delete();
        });

        $category->subCategories()->each(function (Category $item) {
            DeleteCategoryJob::dispatch($item, 0);
        });

        return true;
    }

    public function viewForAdmin(User $context, array $attributes)
    {
        $parentId = Arr::get($attributes, 'parent_id');
        $search   = Arr::get($attributes, 'q');

        $query = $this->getModel()->newQuery()
            ->orderBy('ordering')
            ->with(['subCategories', 'parentCategory']);

        if (!empty($attributes['is_active']))
            $query->where('is_active', 1);
        
        if ($search) {
            return $query->where('name_url', $this->likeOperator(), '%' . $search . '%')->get();
        }

        if (null === $parentId) {
            $query->whereNull('parent_id');
        }

        if (is_numeric($parentId)) {
            $query->where('parent_id', '=', $parentId);
        }

        return $query->get();
    }


    public function getDefaultCategoryParentIds(): array
    {
        $categoryId = 1;

        return Cache::rememberForever($this->getDefaultCategoryParentIdsCacheKey() . "_$categoryId", function () use ($categoryId) {
            return $this->getParentIds($categoryId);
        });
    }

    /**
     * @inheritDoc
     */
    public function getCategoryDefault(): ?Category
    {
        $defaultCategory = Settings::get('sevent.default_category');

        return $this->getModel()->newModelQuery()
            ->where('id', $defaultCategory)->first();
    }

    public function getRelationModel(): CategoryRelation
    {
        return new CategoryRelation();
    }
}
