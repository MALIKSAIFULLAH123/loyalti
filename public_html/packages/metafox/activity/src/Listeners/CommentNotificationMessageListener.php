<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Models\Share;
use MetaFox\Core\Models\Link;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserEntity;

/**
 * Class LikeNotificationToCallbackMessageListener.
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
        if (!$context) {
            return null;
        }

        if (!$user instanceof UserEntity) {
            return null;
        }

        $locale     = $context?->preferredLocale();
        $friendName = $user->name;

        if (!$content instanceof Post && !$content instanceof Share && !$content instanceof Link) {
            return null;
        }

        $title = $this->handleTitle($content, $content->toTitle());
        $owner = $content->owner;
        $your  = __p('comment::phrase.your', [], $locale);

        /* @var string|null $ownerType */
        $ownerType = $owner->hasNamedNotification();

        if ($ownerType) {
            if ($content->userId() != $context->entityId()) {
                $your = $content->userEntity->name;
            }

            return __p('activity::notification.user_commented_to_your_post_in_name', [
                'user'       => $friendName,
                'owner_type' => __p_type_key($ownerType, [], $locale),
                'owner_name' => $content->ownerEntity->name,
                'user_name'  => $your,
            ], $locale);
        }

        $taggedFriends = app('events')->dispatch('friend.get_tag_friend', [$content, $context], true);
        if (!empty($taggedFriends)) {
            return __p('activity::notification.user_commented_on_post_that_you_are_tagged', [
                'user' => $friendName,
            ], $locale);
        }

        if ($content->userEntity->entityId() != $context->entityId()) {
            return __p('activity::notification.user_commented_on_owner_post', [
                'user'    => $friendName,
                'owner'   => $content->userEntity->name,
                'title'   => $title,
                'isTitle' => (int) !empty($title),
            ], $locale);
        }
        // Default message in case no event data is returned
        return __p('activity::notification.user_commented_to_your_post', [
            'user'    => $friendName,
            'title'   => $title,
            'isTitle' => (int) !empty($title),
        ], $locale);
    }

    private function handleTitle(Content $item, string $title): string
    {
        app('events')->dispatch('core.parse_content', [$item, &$title]);

        $title = strip_tags($title);

        return $title;
    }
}
