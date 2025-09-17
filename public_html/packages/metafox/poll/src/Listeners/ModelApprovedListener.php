<?php

namespace MetaFox\Poll\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;
use MetaFox\Poll\Models\Poll;

class ModelApprovedListener
{
    use HasFilterTagUserTrait;

    public function handle(?User $context, Model $model): void
    {
        if (!$model instanceof Poll) {
            return;
        }

        $pendingTagFriend = $model->pending_tagged_friends;

        if (empty($pendingTagFriend)) {
            return;
        }

        $user  = $model->user;
        $owner = $model->owner;

        $model->update(['pending_tagged_friends' => null]);

        if (null === $user || null === $owner) {
            return;
        }

        $data = $this->transformTaggedFriends($user, $user, $owner, $pendingTagFriend);

        $taggedFriends = Arr::get($data, 'tagged_friends');

        if (!is_array($taggedFriends) || !count($taggedFriends)) {
            return;
        }

        app('events')->dispatch(
            'friend.create_tag_friends',
            [$user, $model, $pendingTagFriend, $model->entityType()],
            true
        );
    }
}
