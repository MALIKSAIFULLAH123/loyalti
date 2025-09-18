<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Repositories;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\User;
use MetaFox\Saved\Http\Requests\v1\Saved\StoreRequest;
use MetaFox\Saved\Models\Saved;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SavedRepositoryInterface.
 *
 * @mixin BaseRepository
 * @method Saved find($id, $columns = ['*'])
 * @method Saved getModel()
 */
interface SavedRepositoryInterface
{
    /**
     * @param User $user
     *
     * @return void
     */
    public function deleteForUser(User $user);

    /**
     * @param HasSavedItem $item
     *
     * @return void
     */
    public function deleteForItem(HasSavedItem $item);

    /**
     * @param User                 $context
     * @param array<string, mixed> $attribute
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewSavedItems(User $context, array $attribute): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Saved
     * @throws AuthorizationException
     */
    public function viewSavedItem(User $context, int $id): Saved;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Saved
     * @throws AuthorizationException
     * @throws Exception
     * @see StoreRequest
     */
    public function createSaved(User $context, array $attributes): Saved;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     * @return Saved|null
     */
    public function findSavedItem(User $context, array $attributes): Saved|null;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Saved
     * @throws AuthorizationException
     * @throws Exception
     */
    public function updateSaved(User $context, int $id, array $attributes): Saved;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteSaved(User $context, int $id): bool;

    /**
     * @param User  $context
     * @param array $attribute
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function unSave(User $context, array $attribute): bool;

    /**
     * @param int    $userId
     * @param int    $itemId
     * @param string $itemType
     *
     * @return bool
     */
    public function checkIsSaved(int $userId, int $itemId, string $itemType): bool;

    /**
     * @param Saved $item
     * @param int   $listId
     * @return bool
     */
    public function isAddedToList(Saved $item, int $listId): bool;

    /**
     * @param User  $context
     * @param int   $itemId
     * @param array $listIds
     * @return Saved
     */
    public function addToList(User $context, int $itemId, array $listIds): Saved;

    /**
     * @param User $context
     * @param int  $itemId
     * @return Saved
     */
    public function markAsOpened(User $context, int $itemId): Saved;

    /**
     * @param User $context
     * @param int  $itemId
     * @return Saved
     */
    public function markAsUnOpened(User $context, int $itemId): Saved;

    /**
     * @return array
     */
    public function getAvailableTypes(): array;

    /**
     * @param int $itemId
     * @return Saved
     */
    public function getCollectionByItem(int $itemId): Saved;

    /**
     * @return array
     */
    public function getFilterOptions(): array;

    /**
     * @param User  $context
     * @param array $attributes
     * @return mixed
     */
    public function removeCollectionItem(User $context, array $attributes): mixed;

    /**
     * @param User  $context
     * @param Saved $saved
     */
    public function buildQuerySavedItemUnopened(User $context, Saved $saved): Builder;
}
