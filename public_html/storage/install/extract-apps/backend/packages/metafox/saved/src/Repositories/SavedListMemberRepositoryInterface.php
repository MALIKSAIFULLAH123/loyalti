<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Saved\Models\SavedList;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SavedListMemberRepositoryInterface.
 *
 * @mixin BaseRepository
 * @mixin Builder
 * @method SavedList getModel()
 * @method SavedList find($id, $columns = ['*'])
 * @mixin UserMorphTrait
 */
interface SavedListMemberRepositoryInterface
{
    /**
     * @param  User $context
     * @param  int  $listId
     * @param  int  $userId
     * @return bool
     */
    public function removeMember(User $context, int $listId, int $userId): bool;

    /**
     * @param  int   $collectionId
     * @return array
     */
    public function getInvitedUserIds(int $collectionId): array;

    /**
     * @param  User $context
     * @param  int  $listId
     * @return bool
     */
    public function isSavedListMember(User $context, int $listId): bool;

    /**
     * @param User $context
     * @param int  $listId
     */
    public function viewSavedListMembers(User $context, int $listId): Collection;

    /**
     * @param  User  $user
     * @param  int   $listId
     * @return Model
     */
    public function createMember(User $user, int $listId): Model;
}
