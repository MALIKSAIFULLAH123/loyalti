<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Friend\Repositories\FriendListRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use Prettus\Validator\Exceptions\ValidatorException;

class CreateFriendListListener
{
    public function __construct(protected FriendListRepositoryInterface $listRepository) { }

    /**
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function handle(?User $context, array $attributes): ?FriendList
    {
        if (!$context) {
            return null;
        }

        $name = Arr::get($attributes, 'name', MetaFoxConstant::EMPTY_STRING);
        $userIds = Arr::get($attributes, 'user_ids', []);
        $friendList = $this->listRepository->createFriendList($context, $name);
        
        if (!empty($userIds)) {
            $this->listRepository->addFriendsToFriendLists([$friendList], $userIds);
        }

        return $friendList;
    }
}
