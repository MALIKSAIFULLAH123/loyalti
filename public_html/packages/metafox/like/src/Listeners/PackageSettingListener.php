<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Like\Listeners;

use MetaFox\Like\Models\Like;
use MetaFox\Like\Models\Reaction;
use MetaFox\Like\Notifications\LikeNotification;
use MetaFox\Like\Policies\Handlers\CanLike;
use MetaFox\Like\Policies\LikePolicy;
use MetaFox\Like\Policies\ReactionPolicy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;

/**
 * Class PackageSettingListener.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getEvents(): array
    {
        return [
            'models.notify.created' => [
                ModelCreatedListener::class,
            ],
            'models.notify.deleting' => [
                ModelDeletingListener::class,
            ],
            'like.is_liked' => [
                IsLikedListener::class,
            ],
            'like.user_reacted' => [
                UserReactedListener::class,
            ],
            'like.most_reactions' => [
                MostReactionsListener::class,
            ],
            'like.delete_by_item' => [
                DeleteLikeByItemListener::class,
            ],
            'like.can_like' => [
                CanLikeListener::class,
            ],
            'like.item_reacted.aggregation' => [
                ItemReactedAggregationListener::class,
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            '*' => [
                'like' => UserRole::LEVEL_REGISTERED,
            ],
            Like::ENTITY_TYPE => [
                'view'   => UserRole::LEVEL_GUEST,
                'create' => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'like_notification',
                'module_id'  => 'like',
                'title'      => 'like::phrase.like_notification_type',
                'handler'    => LikeNotification::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 14,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Like::class     => LikePolicy::class,
            Reaction::class => ReactionPolicy::class,
        ];
    }

    public function getPolicyHandlers(): array
    {
        return [
            Like::ENTITY_TYPE => CanLike::class,
        ];
    }
}
