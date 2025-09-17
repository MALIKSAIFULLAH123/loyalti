<?php

namespace MetaFox\Platform\Repositories\Contracts;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;

/**
 * Interface CategoryRepositoryInterface.
 */
interface CategoryRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Model
     */
    public function createCategory(User $context, array $attributes): Model;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function updateCategory(User $context, int $id, array $attributes): Model;

    /**
     * @param Model $category
     * @param int   $newCategoryId
     *
     * @return bool
     */
    public function deleteOrMoveToNewCategory(Model $category, int $newCategoryId): bool;

    /**
     * @return array<int, mixed>
     */
    public function getCategoriesForForm(): array;

    /**
     * @return array<int, mixed>
     */
    public function getCategoriesForStoreForm(?Model $category): array;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return array
     */
    public function getStructure(User $context, array $attributes): array;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Collection
     */
    public function getAllCategories(User $context, array $attributes): Collection;

    /**
     * @return Collection
     */
    public function getCategoryForFilter(): Collection;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @throws AuthorizationException
     */
    public function viewForAdmin(User $context, array $attributes);

    /**
     * @return mixed
     */
    public function clearCache();

    /**
     * @param  ?Model $category
     * @param int     $totalItem
     *
     * @return void
     */
    public function incrementTotalItemCategories(?Model $category, int $totalItem): void;

    /**
     * @param  ?Model $category
     * @param int     $totalItem
     *
     * @return void
     */
    public function decrementTotalItemCategories(?Model $category, int $totalItem): void;

    /**
     * @param array $orderIds
     *
     * @return bool
     */
    public function orderCategories(array $orderIds): bool;

    /**
     * @param int $id
     *
     * @return Model
     */
    public function toggleActive(int $id): Model;

    /**
     * @param Model $category
     * @param array $categoryIds
     *
     * @return void
     */
    public function createCategoryRelationFor(Model $category, array $categoryIds = []): void;

    /**
     * @return void
     */
    public function createTopLevelCategoryRelation(): void;

    /**
     * @return void
     */
    public function createCategoryRelation(): void;

    /**
     * @param Model $category
     *
     * @return void
     */
    public function deleteCategoryRelations(Model $category): void;

    /**
     * @param int $categoryId
     *
     * @return array
     */
    public function getChildrenIds(int $categoryId): array;

    /**
     * @param int $categoryId
     *
     * @return array
     */
    public function getParentIds(int $categoryId): array;

    /**
     * @param string $tableName
     *
     * @return void
     */
    public function migrateCategoryRelationAfterImport(string $tableName): void;

    /**
     * @return void
     */
    public function migrateTopLevelCategoryRelation(): void;

    /**
     * @param Collection $selectedCategories
     *
     * @return array
     */
    public function getCategoriesForUpdateForm(Collection $selectedCategories): array;

    /**
     * @return array
     */
    public function getActiveCategoryIds(): array;

    /**
     * @param int $categoryId
     * @param int $itemId
     *
     * @return bool
     */
    public function hasLinkedItem(int $categoryId, int $itemId): bool;

    /**
     * @param Model $category
     *
     * @return int
     */
    public function isActive(Model $category): int;

    /**
     * @param Model $category
     * @return array
     */
    public function getParentOptionsForDeleteWithMigration(Model $category): array;
}
