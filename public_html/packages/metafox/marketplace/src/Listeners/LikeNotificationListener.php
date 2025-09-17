<?php

namespace MetaFox\Marketplace\Listeners;

use MetaFox\Marketplace\Models\Listing;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserEntity;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UndefinedVariable)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class LikeNotificationListener
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

        $fullName = $user->name;

        $title = $content->toTitle();

        $owner  = $content->owner;
        $locale = $context?->preferredLocale();

        $name = null;

        /*
         * @var string|null $name
         */
        if ($owner instanceof PostBy) {
            $name = $owner->hasNamedNotification();
        }

        if ($name) {
            return __p('marketplace::phrase.user_reacted_to_your_listing_in_name', [
                'user'  => $fullName,
                'title' => $title,
                'name'  => $name,
            ], $locale);
        }

        // Default message in case no event data is returned
        return __p('marketplace::phrase.user_reacted_to_your_listing', [
            'user'  => $fullName,
            'title' => $title,
        ], $locale);
    }
}
