<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Saved\Models\SavedList;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SavedListRepositoryInterface.
 *
 * @mixin BaseRepository
 * @mixin Builder
 * @method SavedList getModel()
 * @method SavedList find($id, $columns = ['*'])
 */
interface SavedListRepositoryInterface
{
    /**
     * @param User $user
     *
     * @return void
     */
    public function deleteForUser(User $user);

    /**
     * @param User                 $user
     * @param array<string, mixed> $attributes
     *
     * @return SavedList
     * @throws AuthorizationException
     */
    public function createSavedList(User $user, array $attributes): SavedList;

    /**
     * @param User $user
     * @param int  $id
     *
     * @return SavedList
     * @throws AuthorizationException
     */
    public function viewSavedList(User $user, int $id): SavedList;

    /**
     * @param User                 $user
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return SavedList
     * @throws AuthorizationException
     */
    public function updateSavedList(User $user, int $id, array $attributes): SavedList;

    /**
     * @param User                 $user
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewSavedLists(User $user, array $attributes): Paginator;

    public function getSavedListByUser(User $user): Collection;

    /**
     * @param User $user
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteSavedList(User $user, int $id): bool;

    /**
     * @param User  $context
     * @param int   $listId
     * @param int[] $friendIds
     *
     * @return int[]
     * @throws AuthorizationException
     */
    public function addFriendToSavedList(User $context, int $listId, array $friendIds = []): void;

    public function filterSavedListByUser(User $context, Collection $savedLists);

    /**
     * @param  User      $user
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewItemCollection(User $user, array $attributes): Paginator;

    public function getTotalSavedLists(User $user, array $attributes);
}
