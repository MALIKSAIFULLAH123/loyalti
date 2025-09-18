<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Repositories;

use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\Contracts\CategoryRepositoryInterface as PlatformRepositoryInterface;
use MetaFox\Video\Models\Category;
use MetaFox\Video\Models\CategoryData;
use MetaFox\Video\Models\CategoryRelation;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface CategoryRepositoryInterface.
 * @mixin BaseRepository
 * @mixin QueryBuilder
 */
interface CategoryRepositoryInterface extends PlatformRepositoryInterface
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
     * @param  Category $category
     * @param  int      $newCategoryId
     * @param  bool     $isDelete
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

    /**
     * @return CategoryData
     */
    public function getCategoryDataModel(): CategoryData;
}
