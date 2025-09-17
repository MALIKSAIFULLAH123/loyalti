<?php

namespace MetaFox\Comment\Listeners;

use MetaFox\Comment\Models\Comment;
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

        if (!$content instanceof Comment) {
            return null;
        }

        $locale      = $context?->preferredLocale();
        $commentText = $content->text;

        app('events')->dispatch('core.parse_content', [$content, &$commentText]);

        $friendName = $user->name;

        if ($content->userId() != $context->entityId()) {
            return __p('like::notification.user_reacted_to_your_comment_comment_mention', [
                'user'    => $friendName,
                'comment' => strip_tags($commentText),
            ], $locale);
        }

        return __p('like::notification.user_reacted_to_your_comment_comment', [
            'user'    => $friendName,
            'comment' => strip_tags($commentText),
        ], $locale);
    }
}
