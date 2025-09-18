<?php

namespace MetaFox\Music\Listeners;

use Illuminate\Auth\AuthenticationException;
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
class CommentNotificationMessageListener
{
    /**
     * @param User|null       $context
     * @param UserEntity|null $user
     * @param Content|null    $content
     *
     * @return string|null
     * @throws AuthenticationException
     */
    public function handle(?User $context, ?UserEntity $user = null, ?Content $content = null): ?string
    {
        if (!$user instanceof UserEntity) {
            return null;
        }

        if (!$context instanceof User) {
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

        return $this->getNotificationMessage($content, $context, $friendName);
    }

    protected function getNotificationMessage(Content $content, User $context, string $friendName): ?string
    {
        $title        = htmlentities($content->toTitle());
        $owner        = $content->owner;
        $userName     = __p('comment::phrase.your');
        $isThemselves = 1;
        $locale       = $context?->preferredLocale();

        /* @var string|null $ownerType */
        $ownerType = $owner->hasNamedNotification();

        if ($content->userId() != $context->entityId()) {
            $userName     = $content->userEntity->name;
            $isThemselves = 0;
        }

        $params = [
            'user'          => $friendName,
            'user_name'     => $userName,
            'item_type'     => $content->entityType(),
            'is_themselves' => $isThemselves,
        ];

        if ($ownerType) {
            return __p('music::notification.user_commented_on_your_item_type_in_owner_type', array_merge($params, [
                'owner_type' => __p_type_key($ownerType, [], $locale),
                'owner_name' => ban_word()->clean($content->ownerEntity->name),
            ]), $locale);
        }

        // Default message in case no event data is returned
        return __p('music::notification.user_commented_on_your_item_type_title', array_merge($params, [
            'title' => $title,
        ]), $locale);
    }
}
