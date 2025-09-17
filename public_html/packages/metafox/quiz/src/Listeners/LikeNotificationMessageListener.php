<?php

namespace MetaFox\Quiz\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Quiz\Models\Quiz;
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
        if (!$user) {
            return null;
        }
        if (!$user instanceof UserEntity) {
            return null;
        }

        if (!$content instanceof Quiz) {
            return null;
        }

        $friendName = $user->name;
        $title      = $content->toTitle();
        $locale     = $context?->preferredLocale();

        /**
         * @var string|null $name
         */
        $name = $content->owner->hasNamedNotification();

        if ($name) {
            return __p('like::notification.user_reacted_to_your_item_type_in_name', [
                'user'       => $friendName,
                'owner_name' => ban_word()->clean($content->ownerEntity->name),
                'content'    => $title,
                'item_type'  => __p_type_key($content->entityType(), [], $locale),
            ], $locale);
        }

        // Default message in case no event data is returned
        return __p('like::notification.user_reacted_to_your_item_type', [
            'user'      => $friendName,
            'title'     => $title,
            'item_type' => __p_type_key($content->entityType(), [], $locale),
        ], $locale);
    }
}
