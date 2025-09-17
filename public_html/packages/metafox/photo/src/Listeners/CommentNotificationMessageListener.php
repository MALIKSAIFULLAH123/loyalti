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
 * Class CommentNotificationMessageListener.
 * @ignore
 */
class CommentNotificationMessageListener
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
        if (!$context instanceof User) {
            return null;
        }

        if (!$user instanceof UserEntity) {
            return null;
        }

        $friendName = $user->name;
        $message    = null;
        $locale     = $context?->preferredLocale();

        if ($content instanceof PhotoGroup) {
            $message = $this->handlePhotoGroup($content, $context, $friendName);
        }

        if (!$content instanceof Content) {
            return null;
        }

        if ($content instanceof Album) {
            return $this->handlePhotoAlbum($content, $context, $friendName);
        }

        if ($message != null) {
            return $message;
        }

        if ($content instanceof PhotoGroup) {
            $aggregateData = $content->statistic?->toAggregateData();

            return __p('photo::notification.user_commented_on_your_photo_group', [
                'user'        => $friendName,
                'total_photo' => $aggregateData['total_photo'],
                'total_video' => $aggregateData['total_video'],
            ], $locale);
        }

        if ($content instanceof Photo) {
            return $this->handlePhoto($content, $context, $friendName);
        }

        return null;
    }

    private function handlePhotoGroup(Content $content, User $context, string $friendName): ?string
    {
        $title  = $content?->toTitle();
        $owner  = $content->owner;
        $locale = $context?->preferredLocale();
        $your   = __p('comment::phrase.your', [], $locale);

        if (MetaFoxConstant::EMPTY_STRING !== $title) {
            app('events')->dispatch('core.parse_content', [$content, &$title]);
        }

        /* @var string|null $ownerType */
        $ownerType     = $owner->hasNamedNotification();
        $taggedFriends = app('events')->dispatch('friend.get_tag_friend', [$content, $context], true);

        if ($ownerType) {
            if (!empty($taggedFriends)) {
                return __p('photo::notification.user_commented_on_post_that_you_are_tagged_in_owner_name', [
                    'user'         => $friendName,
                    'owner_name'   => $content->ownerEntity->name,
                    'feed_content' => $title,
                    'isTitle'      => (int) !empty($title),
                ], $locale);
            }

            if ($content->userId() != $context->entityId()) {
                $your = $content->userEntity->name;
            }

            return __p('photo::notification.user_commented_on_your_photo_in_owner_type', [
                'user'       => $friendName,
                'owner_name' => $content->ownerEntity->name,
                'user_name'  => $your,
            ], $locale);
        }

        if (!empty($taggedFriends)) {
            return __p('photo::notification.user_commented_on_post_that_you_are_tagged', [
                'user' => $friendName,
            ], $locale);
        }

        if ($content->userId() != $context->entityId()) {
            return __p('photo::notification.user_commented_on_owner_photo', [
                'user'    => $friendName,
                'owner'   => $content->userEntity->name,
                'title'   => $title,
                'isTitle' => (int) !empty($title),
            ], $locale);
        }

        // Default message in case no event data is returned
        return null;
    }

    private function handlePhotoAlbum(Album $content, User $context, string $friendName)
    {
        $title = $content?->toTitle();

        $locale = $context?->preferredLocale();

        /* @var string|null $ownerType */
        if ($content->owner instanceof PostBy) {
            $ownerType = $content->owner->hasNamedNotification();
        }

        if ($ownerType) {
            return __p('photo::notification.user_commented_on_your_album_in_owner_type', [
                'user'       => $friendName,
                'owner_type' => __p_type_key($ownerType, [], $locale),
            ], $locale);
        }

        if ($content->userEntity->entityId() != $context->entityId()) {
            return __p('photo::notification.user_commented_on_owner_album', [
                'user'  => $friendName,
                'owner' => $content->userEntity->name,
                'title' => $title,
            ], $locale);
        }

        // Default message in case no event data is returned
        return __p('photo::notification.user_commented_on_your_album', [
            'user'  => $friendName,
            'title' => $title,
        ], $locale);
    }

    protected function handlePhoto(Content $content, User $context, string $friendName): ?string
    {
        $title  = $content?->toTitle();
        $locale = $context?->preferredLocale();

        if ($content->userId() != $context->entityId()) {
            return __p('photo::notification.user_commented_on_owner_photo', [
                'user'    => $friendName,
                'owner'   => $content->userEntity->name,
                'title'   => $title,
                'isTitle' => (int) !empty($title),
            ], $locale);
        }

        // Default message in case no event data is returned
        return __p('photo::notification.user_commented_on_your_photo', [
            'user' => $friendName,
        ], $locale);
    }
}
