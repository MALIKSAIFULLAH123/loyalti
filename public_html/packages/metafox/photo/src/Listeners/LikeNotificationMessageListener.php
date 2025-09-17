<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
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
        if (!$context) {
            return null;
        }
        $friendName = $user->name;
        $locale     = $context?->preferredLocale();
        $message    = null;

        if ($content instanceof PhotoGroup) {
            $message    = $this->handlePhotoGroup($content, $context, $friendName);
        }

        if (!$user instanceof UserEntity) {
            return null;
        }

        if (!$content instanceof Content) {
            return null;
        }

        if ($content instanceof Album) {
            return $this->handlePhotoAlbum($context, $content, $friendName);
        }

        if ($message != null) {
            return $message;
        }

        if ($content instanceof PhotoGroup) {
            $aggregateData = $content->statistic?->toAggregateData();

            return __p('photo::notification.user_reacted_to_your_photo_group', [
                'user'        => $friendName,
                'total_photo' => $aggregateData['total_photo'],
                'total_video' => $aggregateData['total_video'],
            ], $locale);
        }

        if ($content instanceof Photo) {
            return __p('photo::notification.user_reacted_to_your_photo', [
                'user' => $friendName,
            ], $locale);
        }

        return null;
    }

    private function handlePhotoGroup(Content $content, User $context, string $friendName): ?string
    {
        $title  = $content->toTitle();

        if (MetaFoxConstant::EMPTY_STRING !== $title) {
            app('events')->dispatch('core.parse_content', [$content, &$title]);
        }

        $locale = $context?->preferredLocale();

        /* @var string|null $ownerType */
        $ownerType     = $content->owner->hasNamedNotification();

        $taggedFriends = app('events')->dispatch('friend.get_tag_friend', [$content, $context], true);

        if ($ownerType) {
            if (!empty($taggedFriends)) {
                return __p('photo::notification.user_reacted_to_post_that_you_are_tagged_in_owner_name', [
                    'user'         => $friendName,
                    'owner_name'   => $content->ownerEntity->name,
                    'feed_content' => $title,
                    'isTitle'      => (int) !empty($title),
                ], $locale);
            }

            return __p('photo::notification.user_reacted_to_your_photo_in_name', [
                'user'       => $friendName,
                'title'      => $title,
                'owner_name' => $content->ownerEntity->name,
                'isTitle'    => (int) !empty($title),
            ], $locale);
        }

        if (!empty($taggedFriends)) {
            return __p('photo::notification.user_reacted_to_post_you_are_tagged', [
                'user' => $friendName,
            ], $locale);
        }

        return null;
    }

    private function handlePhotoAlbum(User $context, Album $content, string $friendName): string
    {
        $title  = $content->toTitle();
        $locale = $context?->preferredLocale();

        /* @var string|null $ownerType */
        if ($content->owner instanceof PostBy) {
            $ownerType = $content->owner->hasNamedNotification();
        }
        if ($ownerType) {
            return __p('photo::notification.user_reacted_to_your_album_in_name', [
                'user'       => $friendName,
                'owner_name' => $content->ownerEntity->name,
                'owner_type' => __p_type_key($ownerType, [], $locale),
            ], $locale);
        }

        return __p('photo::notification.user_reacted_to_your_album', [
            'user'  => $friendName,
            'title' => $title,
        ], $locale);
    }
}
