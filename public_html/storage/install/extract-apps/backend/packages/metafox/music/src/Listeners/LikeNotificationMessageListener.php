<?php

namespace MetaFox\Music\Listeners;

use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Song;
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
        if (!$user instanceof UserEntity) {
            return null;
        }

        $friendName  = $user->name;

        $entityTypes = [Song::ENTITY_TYPE, Album::ENTITY_TYPE, Playlist::ENTITY_TYPE];

        if (null === $content) {
            return null;
        }

        if (!in_array($content->entityType(), $entityTypes)) {
            return null;
        }

        return $this->getNotificationMessage($content, $friendName);
    }

    private function getNotificationMessage(Content $content, string $friendName): string
    {
        $title = $content->toTitle();
        /**
         * @var string|null $name
         */
        $name = $content->owner->hasNamedNotification();

        $params = [
            'user'      => $friendName,
            'title'     => htmlentities($title),
            'item_type' => $content->entityType(),
        ];

        if ($name) {
            return __p('music::notification.user_reacted_to_your_item_type_in_name', array_merge($params, [
                'owner_name' => ban_word()->clean($content->ownerEntity->name),
                'content'    => htmlentities($title),
            ]));
        }

        // Default message in case no event data is returned
        return __p('music::notification.user_reacted_to_your_item_type', $params);
    }
}
