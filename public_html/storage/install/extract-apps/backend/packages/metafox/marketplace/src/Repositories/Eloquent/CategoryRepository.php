<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use MetaFox\Marketplace\Jobs\DeleteCategoryJob;
use MetaFox\Marketplace\Models\Category;
use MetaFox\Marketplace\Models\CategoryData;
use MetaFox\Marketplace\Models\CategoryRelation;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractCategoryRepository;

/**
 * Class CategoryRepository.
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

        return true;
    }

    public function moveToNewCategory(Category $category, int $newCategoryId, bool $isDelete = false): void
    {
        $totalItem = $category->total_item;
        $parent    = $category?->parentCategory;
        $this->decrementTotalItemCategories($parent, $totalItem);

        $categoryIds    = $category->subCategories()->pluck('id')->toArray();
        $newCategory    = $this->find($newCategoryId);
        $marketplaceIds = $category->marketplaces()->pluck('marketplace_listings.id')->toArray();

        if (!empty($marketplaceIds) && $isDelete) {
            $newCategory->marketplaces()->sync($marketplaceIds, false);
        }

        //update parent_id
        Category::query()->where('parent_id', '=', $category->entityId())->update([
            'parent_id' => $newCategory->entityId(),
            'level'     => $newCategory->level + 1,
        ]);

        $this->deleteCategoryRelations($category);

        if (!empty($categoryIds)) {
            $this->createCategoryRelationFor($newCategory, $categoryIds);
        }
        $this->incrementTotalItemCategories($newCategory, $totalItem);
    }

    public function deleteAllBelongTo(Category $category): bool
    {
        $category->marketplaces()->each(function (Listing $marketplace) {
            $marketplace->delete();
        });

        $category->subCategories()->each(function (Category $item) {
            DeleteCategoryJob::dispatch($item, 0);
        });

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCategoryDefault(): ?Category
    {
        $defaultCategory = Settings::get('marketplace.default_category');

        return $this->getModel()->newModelQuery()
            ->where('id', $defaultCategory)->first();
    }

    public function getDefaultCategoryParentIds(): array
    {
        $categoryId = Settings::get('marketplace.default_category');

        return Cache::rememberForever($this->getDefaultCategoryParentIdsCacheKey() . "_$categoryId", function () use ($categoryId) {
            return $this->getParentIds($categoryId);
        });
    }

    public function getRelationModel(): CategoryRelation
    {
        return new CategoryRelation();
    }

    public function getCategoryDataModel(): CategoryData
    {
        return new CategoryData();
    }
}
