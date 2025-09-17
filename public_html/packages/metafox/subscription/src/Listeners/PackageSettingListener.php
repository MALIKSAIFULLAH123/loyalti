<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;
use MetaFox\Subscription\Jobs\DowngradeSubscription;
use MetaFox\Subscription\Notifications\CompletedTransaction;
use MetaFox\Subscription\Notifications\DeletePackage;
use MetaFox\Subscription\Notifications\ExpiredNotify;
use MetaFox\Subscription\Notifications\ExpiredTransaction;
use MetaFox\Subscription\Notifications\ManualSubscriptionCancellation;
use MetaFox\Subscription\Notifications\PendingTransaction;
use MetaFox\Subscription\Notifications\SystemSubscriptionCancellation;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getUserPermissions(): array
    {
        return [];
    }

    public function getPolicies(): array
    {
        return [];
    }

    public function getSiteSettings(): array
    {
        return [
            'default_downgraded_user_role' => ['value' => UserRole::NORMAL_USER_ID],
            'default_addon_expired_day'    => ['value' => Helper::DEFAULT_EXPIRED_ADDON_DAY],
            'enable_subscription_packages' => ['value' => false],
            'required_on_sign_up'          => ['value' => false],
            'enable_in_app_purchase'       => ['value' => false],
        ];
    }

    public function getEvents(): array
    {
        return [
            'subscription.check_pending_user' => [
                CheckPendingUserListener::class,
            ],
            'payment.payment_pending' => [
                PaymentSuccessProcessedListener::class,
            ],
            'payment.payment_success' => [
                PaymentSuccessProcessedListener::class,
            ],
            'payment.subscription_recycled' => [
                SubscriptionRecycledListener::class,
            ],
            'payment.subscription_expired' => [
                SubscriptionExpiredListener::class,
            ],
            'payment.subscription_cancelled' => [
                SubscriptionCanceledProcessedListener::class,
            ],
            'payment.payment_failure_processing' => [
                PaymentRefundProcessingListener::class,
            ],
            'user.registration.extra_fields.build' => [
                SubscriptionPackageRegistrationFieldsListener::class,
            ],
            'user.registration.extra_fields.values' => [
                SubscriptionPackageRegistrationFieldsValueListener::class,
            ],
            'user.registration.extra_field.rules' => [
                SubscriptionPackageRegistrationFieldRulesListener::class,
            ],
            'user.registration.extra_field.rule_messages' => [
                SubscriptionPackageRegistrationFieldRuleMessagesListener::class,
            ],
            'user.registration.extra_field.create' => [
                SubscriptionPackageRegistrationFieldCreateListener::class,
            ],
            'subscription.invoice.has_pending' => [
                HasPendingInvoiceListener::class,
            ],
            'user.permissions.extra' => [
                UserExtraPermissionListener::class,
            ],
            'user.role.deleted' => [
                UserRoleDeletedListener::class,
            ],
            'user.role.downgrade' => [
                SubscriptionCanceledDowngradeListener::class,
            ],
            'parseRoute' => [
                SubscriptionRouteListener::class,
            ],
            'subscription.pending_user.login' => [
                PendingUserLoginListener::class,
            ],
            'payment.migrate_payment_gateway_id' => [
                MigratePaymentGatewayId::class,
            ],
            'user.deleted' => [
                UserDeletedListener::class,
            ],
            'subscription.get_role_package_by_id' => [
                GetRoleSubscriptionPackageByIdListener::class,
            ],
            'subscription.package_registration' => [
                SubscriptionPackageRegistrationListener::class,
            ],
            'in-app-purchase.get_product_type' => [
                IapGetProductTypeListener::class,
            ],
            'in-app-purchase.create_invoice' => [
                IapCreateInvoiceListener::class,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        $ordering = 0;

        return [
            [
                'type'       => 'subscription_completed_transaction',
                'module_id'  => 'subscription',
                'title'      => 'subscription::phrase.invoice_transaction',
                'handler'    => CompletedTransaction::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'subscription_manual_cancellation',
                'module_id'  => 'subscription',
                'title'      => 'subscription::phrase.subscription_manual_cancellation',
                'handler'    => ManualSubscriptionCancellation::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'subscription_system_cancellation',
                'module_id'  => 'subscription',
                'title'      => 'subscription::phrase.subscription_system_cancellation',
                'handler'    => SystemSubscriptionCancellation::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'subscription_pending_transaction',
                'module_id'  => 'subscription',
                'title'      => 'subscription::phrase.pending_transaction',
                'handler'    => PendingTransaction::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'subscription_expired_transaction',
                'module_id'  => 'subscription',
                'title'      => 'subscription::phrase.expired_transaction',
                'handler'    => ExpiredTransaction::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'subscription_expired_notify',
                'module_id'  => 'subscription',
                'title'      => 'subscription::phrase.expired_notification',
                'handler'    => ExpiredNotify::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'subscription_delete_package',
                'module_id'  => 'subscription',
                'title'      => 'subscription::phrase.delete_package',
                'handler'    => DeletePackage::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(resolve(DowngradeSubscription::class))->hourly();
    }

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return ['subscription_package'];
    }
}
