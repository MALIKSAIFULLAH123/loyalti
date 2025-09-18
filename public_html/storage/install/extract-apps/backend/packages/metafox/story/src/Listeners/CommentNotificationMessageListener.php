<?php

namespace MetaFox\Story\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Models\Story;
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
        if (!$user instanceof UserEntity) {
            return null;
        }

        if (!$context instanceof User) {
            return null;
        }

        $friendName = $user->name;
        $locale = $context?->preferredLocale();

        if (!$content instanceof Story) {
            return null;
        }

        $userName = __p('comment::phrase.your', [], $locale);
        $isThemselves = 1;

        if ($content->userId() != $context->entityId()) {
            $userName = $content->userEntity->name;
            $isThemselves = 0;
        }

        // Default message in case no event data is returned
        return __p('story::notification.user_commented_on_your_story', [
            'user'          => $friendName,
            'user_name'     => $userName,
            'is_themselves' => $isThemselves,
        ], $locale);
    }
}
