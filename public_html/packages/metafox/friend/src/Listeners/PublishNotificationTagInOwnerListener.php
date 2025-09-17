<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Support\Facades\Notification;
use MetaFox\Friend\Models\TagFriend;
use MetaFox\Friend\Notifications\FriendTag;
use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

class PublishNotificationTagInOwnerListener
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
        if (!$model instanceof HasTaggedFriend) {
            return;
        }

        $tagFriends = $this->tagFriendRepository->getItemTagFriends($model);

        foreach ($tagFriends as $tagFriend) {
            if (!$tagFriend instanceof TagFriend) {
                continue;
            }

            $this->toNotification($tagFriend);
        }
    }
    protected function toNotification(TagFriend $tagFriend): void
    {
        $notification = new FriendTag($tagFriend);

        // In case delete models via job
        if (app()->runningInConsole()) {
            Notification::send([], $notification);

            return;
        }

        $item  = $tagFriend->item;
        $owner = $tagFriend->owner;

        if (!$item instanceof Content) {
            return;
        }

        $privacyItem = $item->privacyItem();
        $userItem    = $item->user;

        if (!$privacyItem instanceof Content) {
            return;
        }

        if (!PrivacyPolicy::checkPermission($owner, $privacyItem)) {
            return;
        }

        $pass = app('events')->dispatch('like.owner.notification', [$owner, $item], true);

        if ($pass === false) {
            return;
        }

        if ($owner instanceof HasPrivacyMember) {
            $notifiables = app('events')->dispatch('friend.mention.notifiables', [$userItem, $owner], true);

            if (!is_array($notifiables) || !count($notifiables)) {
                return;
            }

            Notification::send($notifiables, $notification);

            return;
        }

        /* Don't send notifications to users tagged on their timelines posts
         *  Except comment still send notifications to users tagged
         */
        if ($owner->entityId() == $item->ownerId() && !$item instanceof ActionEntity) {
            return;
        }

        if ($userItem->entityId() == $tagFriend->ownerId()) {
            return;
        }

        Notification::send($owner, $notification);
    }
}
