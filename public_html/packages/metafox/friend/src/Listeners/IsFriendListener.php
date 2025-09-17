<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Support\Facades\Cache;
use MetaFox\Friend\Http\Resources\v1\Friend\FriendSimpleCollection;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Friend\Support\CacheManager;
use MetaFox\Platform\Contracts\User;

/**
 * Class IsFriendListener.
 * @ignore
 * @codeCoverageIgnore
 */
class IsFriendListener
{
    /** @var FriendRepositoryInterface */
    public function __construct(protected FriendRepositoryInterface $friendRepository)
    {
    }

    public function handle(mixed $ownerId, mixed $userId): bool
    {
        return $this->friendRepository->isFriend($ownerId, $userId);
    }

    public function getSimpleFriends($user, $owner, $params = [])
    {
        try {
            $friends = $this->friendRepository->viewFriends($user, $owner, $params);

            return new FriendSimpleCollection($friends);
        } catch (\Exception $exception) {
        }
    }
}
