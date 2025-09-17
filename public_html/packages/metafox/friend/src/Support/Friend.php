<?php

namespace MetaFox\Friend\Support;

use Illuminate\Support\Collection;
use MetaFox\Friend\Contracts\FriendContract;
use MetaFox\Friend\Models\FriendRequest;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRequestRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class Friend.
 */
class Friend implements FriendContract
{
    public const FRIENDSHIP_CAN_ADD_FRIEND     = 0;
    public const FRIENDSHIP_IS_FRIEND          = 1;
    public const FRIENDSHIP_CONFIRM_AWAIT      = 2;
    public const FRIENDSHIP_REQUEST_SENT       = 3;
    public const FRIENDSHIP_CAN_NOT_ADD_FRIEND = 4;
    public const FRIENDSHIP_IS_OWNER           = 5;
    public const FRIENDSHIP_IS_UNKNOWN         = 6;
    public const FRIENDSHIP_IS_DENY_REQUEST    = 7;

    public const SHARED_TYPE = 'friend';

    public const VIEW_AVAILABLE_SUGGESTION = 'available_suggestion';

    public function __construct(
        protected FriendRepositoryInterface $friendRepository,
        protected FriendRequestRepositoryInterface $requestRepository,
        protected UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @param User $context
     * @param User $user
     *
     * @return int
     */
    public function getFriendship(User $context, User $user): int
    {
        //Todo: check module friend not active and add test
        //        if () {
        //            return self::FRIENDSHIP_IS_UNKNOWN;
        //        }

        if ($context->entityId() == $user->entityId()) {
            return self::FRIENDSHIP_IS_OWNER;
        }

        if ($this->isFriend($context, $user)) {
            return self::FRIENDSHIP_IS_FRIEND;
        }

        /** @var FriendRequest $requestWait */
        $requestWait = $this->requestRepository->getRequest($context->entityId(), $user->entityId()); //current login user sent request to another user

        //another user sent request to current login user
        /** @var FriendRequest $requestSend */
        $requestSend = $this->requestRepository->getRequest($user->entityId(), $context->entityId());

        if (null != $requestWait) {
            //check deny
            if ($requestWait->is_deny) {
                if (null == $requestSend) {
                    return self::FRIENDSHIP_IS_DENY_REQUEST;
                }

                if ($requestSend->is_deny) {
                    return self::FRIENDSHIP_IS_DENY_REQUEST;
                }

                return self::FRIENDSHIP_CONFIRM_AWAIT;
            }

            return self::FRIENDSHIP_REQUEST_SENT;
        }

        if (null != $requestSend && !$requestSend->is_deny) {
            return self::FRIENDSHIP_CONFIRM_AWAIT;
        }

        if (!$context->can('sendRequest', [FriendRequest::class, $user])) {
            return self::FRIENDSHIP_CAN_NOT_ADD_FRIEND;
        }

        return self::FRIENDSHIP_CAN_ADD_FRIEND;
    }

    /**
     * @param int $userId
     *
     * @return int[]
     */
    public function getFriendIds(int $userId): array
    {
        return $this->friendRepository->getFriendIds($userId);
    }

    public function isFriend(User $user, User $owner): bool
    {
        return $this->friendRepository->isFriend($user->entityId(), $owner->entityId());
    }

    public function getUsersForMention(array $ids): Collection
    {
        $collection = $this->userRepository->getModel()->newModelQuery()
            ->whereIn('id', $ids)
            ->get();

        return $collection->mapWithKeys(function ($page) {
            return [$page->entityId() => $page];
        });
    }
}
