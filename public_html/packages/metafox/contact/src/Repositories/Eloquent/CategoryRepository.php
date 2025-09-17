<?php

namespace MetaFox\Contact\Repositories\Eloquent;

use Illuminate\Support\Facades\Cache;
use MetaFox\Contact\Jobs\DeleteCategoryJob;
use MetaFox\Contact\Models\Category;
use MetaFox\Contact\Models\CategoryRelation;
use MetaFox\Contact\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractCategoryRepository;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Repositories/Eloquent/CategoryRepository.stub.
 */

/**
 * Class CategoryRepository.
 * @property Category $model
 * @method   Category getModel()
 * @method   Category find($id, $columns = ['*'])()
 */
class CategoryRepository extends AbstractCategoryRepository implements CategoryRepositoryInterface
{
    public function model(): string
    {
        return Category::class;
    }

    public function moveToNewCategory(Category $category, int $newCategoryId, bool $isDelete = false): void
    {
        $totalItem = $category->total_item;
        $parent    = $category?->parentCategory;
        $this->decrementTotalItemCategories($parent, $totalItem);

        $newCategory = $this->find($newCategoryId);
        $categoryIds = $category->subCategories()->pluck('id')->toArray();

        //update parent_id
        Category::query()->where('parent_id', '=', $category->entityId())
            ->update([
                'parent_id' => $newCategory->entityId(),
                'level'     => $newCategory->level + 1,
            ]);

        $this->deleteCategoryRelations($category);

        if (!empty($categoryIds)) {
            $this->createCategoryRelationFor($newCategory, $categoryIds);
        }
        $this->incrementTotalItemCategories($newCategory, $totalItem);
    }

    public function deleteCategory(User $context, int $id, int $newCategoryId): bool
    {
        $category = $this->find($id);

        $category->delete();

        DeleteCategoryJob::dispatch($category, $newCategoryId);

        $this->clearCache();

        return true;
    }

    public function deleteAllBelongTo(Category $category): bool
    {
        $category->subCategories()->each(function (Category $item) {
            DeleteCategoryJob::dispatch($item, 0);
        });

        return true;
    }

    public function getRelationModel(): CategoryRelation
    {
        return new CategoryRelation();
    }

    /**
     * @inheritDoc
     */
    public function getDefaultCategoryParentIds(): array
    {
        $categoryId = Settings::get('contact.default_category');
        return Cache::rememberForever($this->getDefaultCategoryParentIdsCacheKey() . "_$categoryId", function () use ($categoryId) {
            return $this->getParentIds($categoryId);
        });
    }
}
