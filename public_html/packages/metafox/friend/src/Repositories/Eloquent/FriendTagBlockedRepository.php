<?php

namespace MetaFox\Friend\Repositories\Eloquent;

use MetaFox\Friend\Models\FriendTagBlocked;
use MetaFox\Friend\Models\TagFriend;
use MetaFox\Friend\Repositories\FriendTagBlockedRepositoryInterface;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class FriendTagBlockedRepository.
 *
 * @method FriendTagBlocked find($id, $columns = ['*'])
 * @method FriendTagBlocked getModel()
 */
class FriendTagBlockedRepository extends AbstractRepository implements FriendTagBlockedRepositoryInterface
{
    use UserMorphTrait;
    public function model()
    {
        return FriendTagBlocked::class;
    }

    /**
     * @param  TagFriend $tagFriend
     * @return bool
     */
    public function createTagBlocked(TagFriend $tagFriend): bool
    {
        $data = [
            'user_id'    => $tagFriend->userId(),
            'user_type'  => $tagFriend->userType(),
            'owner_id'   => $tagFriend->ownerId(),
            'owner_type' => $tagFriend->ownerType(),
            'item_type'  => $tagFriend->itemType(),
            'item_id'    => $tagFriend->itemId(),
        ];
        $this->getModel()->newQuery()->create($data);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function isBlocked(int $ownerId, HasTaggedFriend $item): bool
    {
        $owner = UserEntity::getById($ownerId)->detail;
        $data  = [
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
            'item_type'  => $item->entityType(),
            'item_id'    => $item->entityId(),
        ];

        return $this->getModel()->newQuery()->where($data)->exists();
    }
}
