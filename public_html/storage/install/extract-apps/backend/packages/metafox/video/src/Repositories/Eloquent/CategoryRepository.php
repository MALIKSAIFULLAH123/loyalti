<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractCategoryRepository;
use MetaFox\Video\Jobs\DeleteCategoryJob;
use MetaFox\Video\Models\Category;
use MetaFox\Video\Models\CategoryData;
use MetaFox\Video\Models\CategoryRelation;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;

/**
 * Class CategoryRepository.
 *
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

        $categoryIds = $category->subCategories()->pluck('id')->toArray();
        $newCategory = $this->find($newCategoryId);
        $videoIds    = $category->videos()->pluck('videos.id')->toArray();

        //Move video
        if (!empty($videoIds) && $isDelete) {
            $newCategory->videos()->sync($videoIds, false);
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
        $category->videos()->each(function (Video $video) {
            $video->delete();
        });

        $category->subCategories()->each(function (Category $item) {
            DeleteCategoryJob::dispatch($item, 0);
        });

        return true;
    }

    public function getDefaultCategoryParentIds(): array
    {
        $categoryId = Settings::get('video.default_category');

        return Cache::rememberForever($this->getDefaultCategoryParentIdsCacheKey() . "_$categoryId", function () use ($categoryId) {
            return $this->getParentIds($categoryId);
        });
    }

    /**
     * @inheritDoc
     */
    public function getCategoryDefault(): ?Category
    {
        $defaultCategory = Settings::get('video.default_category');

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
