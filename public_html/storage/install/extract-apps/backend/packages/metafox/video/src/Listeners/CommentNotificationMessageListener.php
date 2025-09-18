<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserEntity;
use MetaFox\Video\Models\Video;

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
        $locale     = $context?->preferredLocale();

        if (!$content instanceof Video) {
            return null;
        }
        $title        = $content->toTitle();
        $owner        = $content->owner;
        $userName     = __p('comment::phrase.your', [], $locale);
        $isThemselves = 1;

        /* @var string|null $ownerType */
        $ownerType = $owner->hasNamedNotification();

        if ($content->userId() != $context?->entityId()) {
            $userName     = $content->userEntity->name;
            $isThemselves = 0;
        }

        if ($ownerType) {
            return __p('comment::notification.user_commented_on_your_item_type_in_owner_type', [
                'user'          => $friendName,
                'owner_type'    => __p_type_key($ownerType, [], $locale),
                'owner_name'    => $content->ownerEntity->name,
                'user_name'     => $userName,
                'item_type'     => __p_type_key($content->entityType(), [], $locale),
                'is_themselves' => $isThemselves,
            ], $locale);
        }

        // Default message in case no event data is returned
        return __p('comment::notification.user_commented_on_your_item_type_title', [
            'user'          => $friendName,
            'title'         => parse_input()->clean($title),
            'user_name'     => $userName,
            'item_type'     => __p_type_key($content->entityType(), [], $locale),
            'is_themselves' => $isThemselves,
        ], $locale);
    }
}
