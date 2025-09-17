<?php

namespace MetaFox\Contact\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Contact\Models\Category;
use MetaFox\Contact\Models\CategoryRelation;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\Contracts\CategoryRepositoryInterface as PlatformRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Repositories/CategoryRepositoryInterface.stub.
 */

/**
 * Interface CategoryRepositoryInterface.
 * @method Category getModel()
 * @method Category find($id, $columns = ['*'])()
 */
interface CategoryRepositoryInterface extends PlatformRepositoryInterface
{
    /**
     * @param Category $category
     * @param int      $newCategoryId
     * @param bool     $isDelete
     * @return void
     */
    public function moveToNewCategory(Category $category, int $newCategoryId, bool $isDelete = false): void;

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
     * @return array
     */
    public function getDefaultCategoryParentIds(): array;

    public function getRelationModel(): CategoryRelation;
}
