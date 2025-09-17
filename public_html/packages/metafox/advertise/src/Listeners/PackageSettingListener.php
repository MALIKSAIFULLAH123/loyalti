<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Advertise\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Advertise\Jobs\HandleEndedSponsorJob;
use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Advertise\Notifications\AdminPaymentSuccessNotification;
use MetaFox\Advertise\Notifications\AdvertiseApprovedNotification;
use MetaFox\Advertise\Notifications\AdvertiseDeniedNotification;
use MetaFox\Advertise\Notifications\MarkAsPaidNotification;
use MetaFox\Advertise\Notifications\MarkSponsorAsPaidNotification;
use MetaFox\Advertise\Notifications\OwnerPaymentSuccessNotification;
use MetaFox\Advertise\Notifications\PendingAdvertiseNotification;
use MetaFox\Advertise\Notifications\PendingSponsorNotification;
use MetaFox\Advertise\Notifications\SponsorApprovedNotification;
use MetaFox\Advertise\Notifications\SponsorDeniedNotification;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getUserPermissions(): array
    {
        return [
            Advertise::ENTITY_TYPE => [
                'view'         => UserRole::LEVEL_GUEST,
                'create'       => UserRole::LEVEL_REGISTERED,
                'update'       => UserRole::LEVEL_REGISTERED,
                'delete'       => UserRole::LEVEL_REGISTERED,
                'hide'         => UserRole::LEVEL_PAGE,
                'auto_approve' => UserRole::LEVEL_REGISTERED,
                'free_payment' => UserRole::LEVEL_ADMINISTRATOR,
            ],
            Sponsor::ENTITY_TYPE   => [
                'view'   => UserRole::LEVEL_GUEST,
                'create' => UserRole::LEVEL_REGISTERED,
                'update' => UserRole::LEVEL_REGISTERED,
                'delete' => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'enable_advertise'                            => ['value' => true],
            'enable_advanced_filter'                      => ['value' => false],
            'maximum_number_of_advertises_on_side_block'  => ['value' => 3],
            'purchase_sponsorship_after_creating_an_item' => ['value' => false],
            'delay_time_to_count_sponsor_view'            => ['value' => 3],
        ];
    }

    public function getEvents(): array
    {
        return [
            'payment.payment_success_processed'                    => [
                PaymentSuccessListener::class,
            ],
            'payment.payment_pending_processed'                    => [
                PaymentPendingListener::class,
            ],
            'advertise.sponsorship.sponsor_free'                   => [
                SponsorFreeListener::class,
            ],
            'advertise.sponsorship.unsponsor'                      => [
                UnsponsorListener::class,
            ],
            'advertise.sponsorship.get_sponsored_item_ids_by_type' => [
                GetSponsoredItemIdsByTypeListener::class,
            ],
            'advertise.sponsor.sponsor_feed_free'                  => [
                SponsorFeedFreeListener::class,
            ],
            'advertise.sponsor.unsponsor_feed'                     => [
                UnsponsorFeedListener::class,
            ],
            'models.notify.deleted'                                => [
                ModelDeletedListener::class,
            ],
            'advertise.sponsor.delete_by_item'                     => [
                DeleteSponsorsByItemListener::class,
            ],
            'payment.migrate_payment_gateway_id'                   => [
                MigratePaymentGatewayId::class,
            ],
            'advertise.can_purchase_sponsor'                       => [
                CanPurchaseSponsorListener::class,
            ],
            'advertise.ask_for_sponsorship'                        => [
                AskForSponsorshipListener::class,
            ]
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'advertise_approved_notification',
                'module_id'  => 'advertise',
                'handler'    => AdvertiseApprovedNotification::class,
                'title'      => 'advertise::phrase.approve_ad_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'advertise_sponsor_approved_notification',
                'module_id'  => 'advertise',
                'handler'    => SponsorApprovedNotification::class,
                'title'      => 'advertise::phrase.approve_sponsor_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'advertise_denied_notification',
                'module_id'  => 'advertise',
                'handler'    => AdvertiseDeniedNotification::class,
                'title'      => 'advertise::phrase.deny_ad_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'advertise_sponsor_denied_notification',
                'module_id'  => 'advertise',
                'handler'    => SponsorDeniedNotification::class,
                'title'      => 'advertise::phrase.deny_sponsor_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'advertise_sponsor_pending_notification',
                'module_id'  => 'advertise',
                'handler'    => PendingSponsorNotification::class,
                'title'      => 'advertise::phrase.pending_sponsor_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'advertise_pending_notification',
                'module_id'  => 'advertise',
                'handler'    => PendingAdvertiseNotification::class,
                'title'      => 'advertise::phrase.pending_advertise_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'advertise_mark_as_paid_notification',
                'module_id'  => 'advertise',
                'handler'    => MarkAsPaidNotification::class,
                'title'      => 'advertise::phrase.mark_ad_as_paid_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'advertise_payment_success_ad_notification',
                'module_id'  => 'advertise',
                'handler'    => AdminPaymentSuccessNotification::class,
                'title'      => 'advertise::phrase.pay_ad_successfully_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'advertise_mark_sponsor_as_paid_notification',
                'module_id'  => 'advertise',
                'handler'    => MarkSponsorAsPaidNotification::class,
                'title'      => 'advertise::phrase.mark_sponsor_as_paid_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'advertise_payment_success_owner_notification',
                'module_id'  => 'advertise',
                'handler'    => OwnerPaymentSuccessNotification::class,
                'title'      => 'advertise::phrase.owner_payment_success_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule)
    {
        $schedule->job(HandleEndedSponsorJob::class)->hourly();
    }
}
