<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\User;

class ModelDeletingListener
{
    /**
     * @param TagFriendRepositoryInterface $tagFriendRepository
     */
    public function __construct(protected TagFriendRepositoryInterface $tagFriendRepository)
    {
    }

    /**
     * @param mixed $model
     */
    public function handle($model): void
    {
        if ($model instanceof HasTaggedFriend) {
            $this->tagFriendRepository->deleteItemTagFriends($model);
        }

        if ($model instanceof User) {
            $ownerIds = Settings::get('user.on_signup_new_friend');

            if (!is_array($ownerIds)) {
                return;
            }

            if (!in_array($model->entityId(), $ownerIds)) {
                return;
            }

            $kept = array_filter($ownerIds, function ($ownerId) use ($model) {
                return $ownerId != $model->entityId();
            });

            $kept = array_values($kept);

            Settings::save([
                'user' => [
                    'on_signup_new_friend' => $kept
                ]
            ]);
        }
    }
}
