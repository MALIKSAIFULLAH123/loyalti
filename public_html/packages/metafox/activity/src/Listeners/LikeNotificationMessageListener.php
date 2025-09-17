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

        if (!$user instanceof UserEntity) {
            return null;
        }

        $locale        = $context?->preferredLocale();
        $friendName    = $user->name;
        $taggedFriends = app('events')->dispatch('friend.get_tag_friend', [$content, $context], true);

        if ($content instanceof Post || $content instanceof Link) {
            $title = $this->handleTitle($content, $content->toTitle());
            $owner = $content->owner;

            /* @var string|null $ownerType */
            $ownerType = $owner->hasNamedNotification();

            if ($ownerType) {
                if (!empty($taggedFriends)) {
                    return __p(
                        'activity::notification.user_reacted_to_post_that_you_are_tagged_in_owner_name',
                        [
                            'user'         => $friendName,
                            'owner_name'   => $content->ownerEntity->name,
                            'feed_content' => $title,
                            'isTitle'      => (int) !empty($title),
                        ],
                        $locale
                    );
                }

                return __p('activity::notification.user_reacted_to_your_post_in_name', [
                    'user'       => $friendName,
                    'title'      => $title,
                    'owner_name' => $content->ownerEntity->name,
                    'isTitle'    => (int) !empty($title),
                ], $locale);
            }

            if (!empty($taggedFriends)) {
                return __p('activity::notification.user_reacted_to_post_you_are_tagged_title', [
                    'user'         => $friendName,
                    'feed_content' => $title,
                    'isTitle'      => (int) !empty($title),
                ], $locale);
            }

            // Default message in case no event data is returned
            return __p('activity::notification.user_reacted_to_your_post', [
                'user'    => $friendName,
                'title'   => $title,
                'isTitle' => (int) !empty($title),
            ], $locale);
        }

        if ($content instanceof Share) {
            return __p('activity::notification.user_reacted_to_a_post_you_shared', [
                'user' => $friendName,
            ], $locale);
        }

        return null;
    }

    private function handleTitle(Content $item, string $title): string
    {
        app('events')->dispatch('core.parse_content', [$item, &$title]);

        $title = strip_tags($title);

        return $title;
    }
}
