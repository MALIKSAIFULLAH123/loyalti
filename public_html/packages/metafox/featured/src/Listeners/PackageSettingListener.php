<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Featured\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Featured\Jobs\MarkItemsEndedJob;
use MetaFox\Featured\Notifications\CancelledFeaturedItemForDeletedContentNotification;
use MetaFox\Featured\Notifications\CancelledFeaturedItemNotification;
use MetaFox\Featured\Notifications\CancelledInvoiceNotification;
use MetaFox\Featured\Notifications\EndedFeaturedItemNotification;
use MetaFox\Featured\Notifications\MarkedInvoiceAsPaidNotification;
use MetaFox\Featured\Notifications\SuccessPaymentNotification;
use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getEvents(): array
    {
        return [
            'user.deleted' => [
                DeletedUserListener::class,
            ],
            'models.notify.deleted' => [
                ModelDeletedListener::class,
            ],
            'payment.payment_success_processed' => [
                SuccessPaymentListener::class,
            ],
            'payment.payment_pending_processed' => [
                SuccessPaymentListener::class,
            ],
            'featured.item.feature_free' => [
                FeatureItemFreeListener::class,
            ],
            'featured.item.unfeature' => [
                UnfeatureItemListener::class,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'featured_cancelled_item_for_deleted_content',
                'module_id'  => 'featured',
                'handler'    => CancelledFeaturedItemForDeletedContentNotification::class,
                'title'      => 'featured::phrase.cancelled_featured_item_for_deleted_content_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'featured_cancelled_item',
                'module_id'  => 'featured',
                'handler'    => CancelledFeaturedItemNotification::class,
                'title'      => 'featured::phrase.cancelled_featured_item_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'featured_cancelled_invoice',
                'module_id'  => 'featured',
                'handler'    => CancelledInvoiceNotification::class,
                'title'      => 'featured::phrase.cancelled_invoice_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'featured_ended_item',
                'module_id'  => 'featured',
                'handler'    => EndedFeaturedItemNotification::class,
                'title'      => 'featured::phrase.ended_featured_item_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'featured_marked_invoice_as_paid',
                'module_id'  => 'featured',
                'handler'    => MarkedInvoiceAsPaidNotification::class,
                'title'      => 'featured::phrase.marked_invoice_as_paid_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'featured_payment_success',
                'module_id'  => 'featured',
                'handler'    => SuccessPaymentNotification::class,
                'title'      => 'featured::phrase.success_payment_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(MarkItemsEndedJob::class)->hourly()->withoutOverlapping();
    }
}
