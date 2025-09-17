<?php

namespace MetaFox\Marketplace\Listeners;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Marketplace\Models\Listing;
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
        if (!$user instanceof UserEntity) {
            return null;
        }

        if (!$content instanceof Listing) {
            return null;
        }

        if (!$context instanceof User) {
            return null;
        }

        $locale     = $context?->preferredLocale();
        $friendName = $user->name;

        $title = $content->toTitle();

        /**
         * @var string|null $ownerType
         */
        $ownerType = $content->owner->hasNamedNotification();

        if ($ownerType) {
            return __p('marketplace::phrase.user_commented_to_your_listing_in_name', [
                'user' => $friendName,
                'name' => $content->ownerEntity->name,
            ], $locale);
        }

        if ($content->userEntity->entityId() != $context->entityId()) {
            return __p('marketplace::phrase.user_commented_on_owner_listing', [
                'user'  => $friendName,
                'owner' => $content->userEntity->name,
                'title' => $title,
            ], $locale);
        }

        // Default message in case no event data is returned
        return __p('marketplace::phrase.user_commented_to_your_listing', [
            'user'  => $friendName,
            'title' => $title,
        ], $locale);
    }
}
