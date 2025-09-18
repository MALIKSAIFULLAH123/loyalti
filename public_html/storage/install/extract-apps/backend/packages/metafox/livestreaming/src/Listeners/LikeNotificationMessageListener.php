<?php

namespace MetaFox\LiveStreaming\Listeners;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserEntity;

/**
 * Class LikeNotificationToCallbackMessageListener.
 * @ignore
 */
class LikeNotificationMessageListener
{
    /**
     * @param User|null       $context
     * @param UserEntity|null $user
     * @param Content|null    $content
     *
     * @return string|null
     */
    public function handle(?User $context, ?UserEntity $user = null, ?Content $content = null): ?string
    {
        if (!$user instanceof UserEntity) {
            return null;
        }

        if (!$content instanceof LiveVideo) {
            return null;
        }

        $friendName = $user->name;
        $title      = htmlspecialchars($content->toNotificationTitle());
        $locale     = $context?->preferredLocale();
        /**
         * @var string|null $name
         */
        $name          = $content->owner->hasNamedNotification();
        $taggedFriends = app('events')->dispatch('friend.get_tag_friend', [$content, $context], true);

        if ($name) {
            if (!empty($taggedFriends)) {
                return __p('livestreaming::notification.user_reacted_to_live_video_that_you_are_tagged_in_owner_name', [
                    'user'         => $friendName,
                    'owner_name'   => $content->ownerEntity->name,
                    'feed_content' => $title,
                    'isTitle'      => (int) !empty($title),
                ], $locale);
            }

            return __p('like::notification.user_reacted_to_your_item_type_in_name', [
                'user'       => $friendName,
                'owner_name' => $content->ownerEntity->name,
                'content'    => $title,
                'item_type'  => __p_type_key($content->entityType(), [], $locale),
            ], $locale);
        }

        if (!empty($taggedFriends)) {
            return __p('livestreaming::notification.user_reacted_to_live_video_you_are_tagged', [
                'user'    => $friendName,
                'title'   => $title,
                'isTitle' => !empty($title),
            ], $locale);
        }

        // Default message in case no event data is returned
        return __p('livestreaming::notification.user_reacted_to_your_live_video', [
            'user'    => $friendName,
            'title'   => $title,
            'isTitle' => !empty($title),
        ], $locale);
    }
}
