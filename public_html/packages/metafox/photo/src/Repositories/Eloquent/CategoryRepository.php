<?php

namespace MetaFox\Photo\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use MetaFox\Photo\Jobs\DeleteCategoryJob;
use MetaFox\Photo\Models\Category;
use MetaFox\Photo\Models\CategoryData;
use MetaFox\Photo\Models\CategoryRelation;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractCategoryRepository;

/**
 * Class CategoryRepository.
 * @property Category $model
 * @method   Category getModel()
 * @method   Category find($id)
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

    public function deleteCategory(User $context, int $id, array $attributes): bool
    {
        $category = $this->find($id);

        $category->delete();

        $newCategoryId = $attributes['new_category_id'] ?? 0;

        DeleteCategoryJob::dispatch($category, $newCategoryId);

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
        $photoIds    = $category->photos()->pluck('photos.id')->toArray();

        //Move photo
        if (!empty($photoIds) && $isDelete) {
            $newCategory->photos()->sync($photoIds, false);
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
        $category->photos()->each(function (Photo $photo) {
            $photo->delete();
        });

        $category->subCategories()->each(function (Category $item) {
            DeleteCategoryJob::dispatch($item, 0);
        });

        return true;
    }

    public function getDefaultCategoryParentIds(): array
    {
        $categoryId = Settings::get('photo.default_category');

        return Cache::rememberForever($this->getDefaultCategoryParentIdsCacheKey() . "_$categoryId", function () use ($categoryId) {
            return $this->getParentIds($categoryId);
        });
    }

    /**
     * @inheritDoc
     */
    public function getCategoryDefault(): ?Category
    {
        $defaultCategory = Settings::get('photo.default_category');

        return $this->getModel()->newModelQuery()
            ->where('id', $defaultCategory)->first();
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
