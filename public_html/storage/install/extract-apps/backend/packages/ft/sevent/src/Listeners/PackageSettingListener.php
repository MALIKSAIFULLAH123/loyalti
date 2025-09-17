<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace Foxexpert\Sevent\Listeners;

use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Models\Ticket;
use Foxexpert\Sevent\Models\Category;
use Foxexpert\Sevent\Notifications\SeventApproveNotification;
use Foxexpert\Sevent\Policies\SeventPolicy;
use Foxexpert\Sevent\Policies\TicketPolicy;
use Foxexpert\Sevent\Policies\CategoryPolicy;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\MetaFoxPrivacy;
use Foxexpert\Sevent\Notifications\OwnerPaymentSuccessNotification;
use Foxexpert\Sevent\Notifications\PaymentPendingNotification;
use Foxexpert\Sevent\Notifications\PaymentSuccessNotification;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    /**
     * @return string[]
     */
    public function getCaptchaRules(): array
    {
        return ['create_sevent'];
    }

    public function getActivityTypes(): array
    {
        return [
            [
                'type'                   => Sevent::ENTITY_TYPE,
                'entity_type'            => Sevent::ENTITY_TYPE,
                'is_active'              => true,
                'title'                  => 'sevent::phrase.sevent_type',
                'description'            => 'added_a_sevent',
                'is_system'              => 0,
                'can_comment'            => true,
                'can_like'               => true,
                'can_share'              => true,
                'can_edit'               => false,
                'can_create_feed'        => true,
                'can_redirect_to_detail' => true,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'sevent_payment_pending_notification',
                'module_id'  => 'sevent',
                'title'      => 'sevent::phrase.payment_pending_notification',
                'handler'    => PaymentPendingNotification::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 14,
            ],
            [
                'type'       => 'sevent_payment_success_notification',
                'module_id'  => 'sevent',
                'title'      => 'sevent::phrase.payment_success_notification',
                'handler'    => PaymentSuccessNotification::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 14,
            ],
            [
                'type'       => 'sevent_owner_payment_success_notification',
                'module_id'  => 'sevent',
                'title'      => 'sevent::phrase.owner_payment_success_notification',
                'handler'    => OwnerPaymentSuccessNotification::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 14,
            ],
            [
                'type'       => 'sevent_approve_notification',
                'module_id'  => 'sevent',
                'handler'    => SeventApproveNotification::class,
                'title'      => 'sevent::phrase.sevent_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 18,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Sevent::class     => SeventPolicy::class,
            Ticket::class     => TicketPolicy::class,
            Category::class => CategoryPolicy::class,
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Sevent::ENTITY_TYPE => [
                'view'     => UserRole::LEVEL_GUEST,
                'create'   => UserRole::LEVEL_REGISTERED,
                'update'   => UserRole::LEVEL_REGISTERED,
                'delete'   => UserRole::LEVEL_REGISTERED,
                'moderate' => UserRole::LEVEL_ADMINISTRATOR,
                'feature'  => UserRole::LEVEL_ADMINISTRATOR,
                'approve'  => UserRole::LEVEL_STAFF,
                'save'     => UserRole::LEVEL_REGISTERED,
                'like'     => UserRole::LEVEL_REGISTERED,
                'share'    => UserRole::LEVEL_REGISTERED,
                'comment'  => UserRole::LEVEL_REGISTERED,
                'report'   => UserRole::LEVEL_REGISTERED,
                'sponsor'  => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'sponsor_free' => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
                'sponsor_in_feed' => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'auto_publish_sponsored_item' => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
                'auto_approved' => UserRole::LEVEL_REGISTERED,
            ],
            Ticket::ENTITY_TYPE => [
                'view'     => UserRole::LEVEL_GUEST,
                'create'   => UserRole::LEVEL_REGISTERED,
                'update'   => UserRole::LEVEL_REGISTERED,
                'delete'   => UserRole::LEVEL_REGISTERED,
                'moderate' => UserRole::LEVEL_ADMINISTRATOR,
                'like'     => UserRole::LEVEL_REGISTERED,
                'share'    => UserRole::LEVEL_REGISTERED,
                'comment'  => UserRole::LEVEL_REGISTERED,
                'report'   => UserRole::LEVEL_REGISTERED
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'enable_online' => ['value' => 1],
            'enable_location' => ['value' => 1],
            'enable_host' => ['value' => 1],

            'enable_add_calendar'         => ['value' => 1],
            'time_format'          => ['value' => 12],
            'time_zone'          => ['value' => 'America/New_York'],
            'enable_terms'         => ['value' => 1],
            'enable_activity_point'         => ['value' => 0],
            'sevent.purchase_sponsor_price' => [
                'value'     => '',
                'is_public' => false,
            ]
        ];
    }

    public function getEvents(): array
    {
        return [
            'payment.payment_success_processed' => [
                PaymentSuccessListener::class,
            ],
            'payment.payment_pending_processed' => [
                PaymentPendingListener::class,
            ],
            'payment.gateway.has_access' => [
                PaymentHasAccessListener::class,
            ],
            'models.notify.updated' => [
                ModelUpdatedListener::class,
            ],
            'activity.update_feed_item_privacy' => [
                UpdateFeedItemPrivacyListener::class,
            ],
            'like.notification_to_callback_message' => [
                LikeNotificationMessageListener::class,
            ],
            'comment.notification_to_callback_message' => [
                CommentNotificationMessageListener::class,
            ],
            'core.collect_total_items_stat' => [
                CollectTotalItemsStatListener::class,
            ],
            'user.deleted' => [
                UserDeletedListener::class,
            ],
            'advertise.sponsor.enable_sponsor_feed' => [
                EnableSponsorFeedListener::class,
            ],
            'advertise.sponsor.disable_sponsor_feed' => [
                DisableSponsorFeedListener::class,
            ],
            'importer.completed' => [
                ImporterCompleted::class,
            ],
        ];
    }

    public function getUserPrivacy(): array
    {
        return [
            'sevent.share_sevents' => [
                'phrase' => 'sevent::phrase.user_privacy.who_can_share_sevents',
            ],
            'sevent.view_browse_sevents' => [
                'phrase' => 'sevent::phrase.user_privacy.who_can_view_sevents',
            ],
        ];
    }

    public function getUserPrivacyResource(): array
    {
        return [
            'page' => [
                'sevent.share_sevents',
                'sevent.view_browse_sevents',
            ],
            'group' => [
                'sevent.share_sevents',
            ],
        ];
    }

    public function getDefaultPrivacy(): array
    {
        return [
            Sevent::ENTITY_TYPE => [
                'phrase'  => 'sevent::phrase.sevents',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getProfileMenu(): array
    {
        return [
            Sevent::ENTITY_TYPE => [
                'phrase'  => 'sevent::phrase.sevents',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            Sevent::ENTITY_TYPE => [
                'flood_control' => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'quota_control' => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
            ],
        ];
    }

    public function getItemTypes(): array
    {
        return [
            Sevent::ENTITY_TYPE,
            Ticket::ENTITY_TYPE,
        ];
    }

    /**
     * @return string[]|null
     */
    public function getSiteStatContent(): ?array
    {
        return [
            Sevent::ENTITY_TYPE => ['icon' => 'ico-newspaper-o'],
            'pending_sevent'    => [
                'icon' => 'ico-clock-o',
                'to'   => '/sevent/pending',
            ],
        ];
    }

    public function getSavedTypes(): array
    {
        return [
            [
                'label' => __p('sevent::phrase.sevents'),
                'value' => 'sevent',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return [
            'sevent',
            'sevent_category',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function getAdMobPages(): array
    {
        return [
            [
                'path' => '/sevent',
                'name' => 'sevent::phrase.ad_mob_home_page',
            ],
            [
                'path' => '/sevent/:id',
                'name' => 'sevent::phrase.ad_mob_detail_page',
            ],
        ];
    }
}
