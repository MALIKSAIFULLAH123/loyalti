<?php

namespace MetaFox\Event\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use MetaFox\Event\Jobs\DeleteCategoryJob;
use MetaFox\Event\Models\Category;
use MetaFox\Event\Models\CategoryData;
use MetaFox\Event\Models\CategoryRelation;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\CategoryRepositoryInterface;
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
    public function model()
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
        $eventIds    = $category->events()->pluck('events.id')->toArray();
        $categoryIds = $category->subCategories()->pluck('id')->toArray();

        //Move event
        if (!empty($eventIds) && $isDelete) {
            $newCategory->events()->sync($eventIds, false);
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
        $category->events()->each(function (Event $event) {
            $event->delete();
        });

        $category->subCategories()->each(function (Category $item) {
            DeleteCategoryJob::dispatch($item, 0);
        });

        return true;
    }

    public function getDefaultCategoryParentIds(): array
    {
        $categoryId = Settings::get('event.default_category');

        return Cache::rememberForever($this->getDefaultCategoryParentIdsCacheKey() . "_$categoryId", function () use ($categoryId) {
            return $this->getParentIds($categoryId);
        });
    }

    /**
     * @inheritDoc
     */
    public function getCategoryDefault(): ?Category
    {
        $defaultCategory = Settings::get('event.default_category');

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
