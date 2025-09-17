<?php

namespace Foxexpert\Sevent\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Foxexpert\Sevent\Models\Category;
use Foxexpert\Sevent\Models\CategoryRelation;
use MetaFox\Platform\Contracts\User;

/**
 * Interface CategoryRepositoryInterface.
 * @method Category getModel()
 * @method Category find($id, $columns = ['*'])()
 */
interface CategoryRepositoryInterface
{
    /**
     * @param User $context
     * @param int  $id
     *
     * @return Category
     * @throws AuthorizationException
     */
    public function viewCategory(User $context, int $id): Category;

    /**
     * @param User $context
     * @param int  $id
     * @param int  $newCategoryId
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteCategory(User $context, int $id, int $newCategoryId): bool;

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function deleteAllBelongTo(Category $category): bool;

    /**
     * @param Category $category
     * @param int      $newCategoryId
     * @param bool     $isDelete
     * @return void
     */
    public function moveToNewCategory(Category $category, int $newCategoryId, bool $isDelete = false): void;

    /**
     * @return Category|null
     */
    public function getCategoryDefault(): ?Category;

    /**
     * @return array
     */
    public function getDefaultCategoryParentIds(): array;

    public function getRelationModel(): CategoryRelation;
}
