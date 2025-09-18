<?php

namespace MetaFox\Page\Repositories\Eloquent;

use Illuminate\Support\Facades\Cache;
use MetaFox\Page\Jobs\DeletePageCategoryJob;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\CategoryRelation;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractCategoryRepository;

/**
 * Class PageCategoryRepository.
 * @method Category getModel()
 * @method Category find($id, $columns = ['*'])
 */
class PageCategoryRepository extends AbstractCategoryRepository implements PageCategoryRepositoryInterface
{
    public function model(): string
    {
        return Category::class;
    }

    public function viewCategory(User $context, int $id): Category
    {
        return $this->find($id);
    }

    public function deleteCategory(User $context, int $id, int $newCategoryId, int $newTypeId): bool
    {
        $category = $this->find($id);

        $category->delete();

        DeletePageCategoryJob::dispatchSync($category, $newCategoryId, $newTypeId);

        $this->clearCache();

        return true;
    }

    public function moveToNewCategory(Category $category, int $newCategoryId, bool $isDelete = false): void
    {
        $totalItem = $category->total_item;
        $parent    = $category?->parentCategory;
        $this->decrementTotalItemCategories($parent, $totalItem);

        $categoryIds = $category->subCategories()->pluck('id')->toArray();
        $newCategory = $this->find($newCategoryId);
        $pageIds     = $category->pages()->pluck('pages.id')->toArray();

        if (!empty($pageIds) && $isDelete) {
            //Move page
            Page::query()->whereIn('id', $pageIds)
                ->update([
                    'category_id' => $newCategoryId,
                ]);
        }

        //update parent_id
        Category::query()
            ->where('parent_id', '=', $category->entityId())
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

    public function deleteAllBelongTo(Category $category): bool
    {
        $category->pages()->each(function (Page $page) {
            $page->delete();
        });

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCategoryDefault(): ?Category
    {
        $defaultCategory = Settings::get('page.default_category');

        return $this->getModel()->newModelQuery()
            ->where('id', $defaultCategory)->first();
    }

    public function getDefaultCategoryParentIds(): array
    {
        $categoryId = Settings::get('page.default_category');

        return Cache::rememberForever($this->getDefaultCategoryParentIdsCacheKey() . "_$categoryId", function () use ($categoryId) {
            return $this->getParentIds($categoryId);
        });
    }

    public function getRelationModel(): CategoryRelation
    {
        return new CategoryRelation();
    }

    public function hasLinkedItem(int $categoryId, int $itemId): bool
    {
        return Page::query()->newQuery()->where([
            'id'          => $itemId,
            'category_id' => $categoryId,
        ])->exists();
    }
}
