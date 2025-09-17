<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\EMoney\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\EMoney\Jobs\ApprovePendingTransactionJob;
use MetaFox\EMoney\Jobs\GetExchangeRateForBaseJob;
use MetaFox\EMoney\Jobs\GetExchangeRateJob;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Notifications\ApprovedTransactionNotification;
use MetaFox\EMoney\Notifications\DeniedWithdrawRequestNotification;
use MetaFox\EMoney\Notifications\PendingWithdrawRequestNotification;
use MetaFox\EMoney\Notifications\ReduceAmountFromBalanceNotification;
use MetaFox\EMoney\Notifications\SendAmountToBalanceNotification;
use MetaFox\EMoney\Notifications\SuccessPaymentRequestNotification;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\Support\Browse\Browse;

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
    public function getSiteSettings(): array
    {
        return [
            'minimum_withdraw'         => [
                'value' => [
                    'USD' => 100,
                ],
                'type'  => 'array',
            ],
            'balance_holding_duration' => [
                'value' => 0,
            ],
            'withdraw_fee'             => [
                'value' => 0,
                'type'  => 'float',
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'ewallet.get_exchange_rate'                    => [
                GetExchangeRateListener::class,
            ],
            'ewallet.get_conversed_amount'                 => [
                GetConversedAmountListener::class,
            ],
            'payment.payment_success'                      => [
                PaymentSuccessListener::class,
            ],
            'core.collect_total_items_stat'                => [
                CollectTotalItemsStatListener::class,
            ],
            'ewallet.transaction.create'                   => [
                CreateTransactionListener::class,
            ],
            'ewallet.transaction.available_conversion'     => [
                AvailableForTransactionConversion::class,
            ],
            'parseRoute'                                   => [
                ParseRouteListener::class,
            ],
            'payment.place_order_processed'                => [
                PlaceOrderProcessedListener::class,
            ],
            'payment.migrate_payment_gateway_id'           => [
                MigratePaymentGatewayId::class,
            ],
            'payment.multistep_form.before_payment'        => [
                MultiStepFormListener::class,
            ],
            'ewallet.exchange_rate.get_by_target_and_base' => [
                GetSpecificExchangeRateListener::class,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'ewallet_send_amount_to_balance',
                'module_id'  => 'ewallet',
                'handler'    => SendAmountToBalanceNotification::class,
                'title'      => 'ewallet::notification.ewallet_send_amount_to_balance_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 10,
            ],
            [
                'type'       => 'ewallet_reduce_amount_from_balance',
                'module_id'  => 'ewallet',
                'handler'    => ReduceAmountFromBalanceNotification::class,
                'title'      => 'ewallet::notification.ewallet_reduce_amount_from_balance_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 10,
            ],
            [
                'type'       => 'ewallet_approved_transaction_notification',
                'module_id'  => 'ewallet',
                'handler'    => ApprovedTransactionNotification::class,
                'title'      => 'ewallet::phrase.approved_balance_transaction_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 10,
            ],
            [
                'type'       => 'ewallet_pending_withdraw_request',
                'module_id'  => 'ewallet',
                'handler'    => PendingWithdrawRequestNotification::class,
                'title'      => 'ewallet::phrase.approve_the_withdrawal_request_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 10,
            ],
            [
                'type'       => 'ewallet_denied_withdraw_request',
                'module_id'  => 'ewallet',
                'handler'    => DeniedWithdrawRequestNotification::class,
                'title'      => 'ewallet::phrase.denied_withdrawal_request_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 10,
            ],
            [
                'type'       => 'ewallet_success_payment_withdraw_request',
                'module_id'  => 'ewallet',
                'handler'    => SuccessPaymentRequestNotification::class,
                'title'      => 'ewallet::phrase.withdrawal_request_success_payment_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 10,
            ],
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(ApprovePendingTransactionJob::class)->hourly()->withoutOverlapping();
        $schedule->job(GetExchangeRateJob::class)->hourly()->withoutOverlapping();
        $schedule->job(GetExchangeRateForBaseJob::class)->hourly()->withoutOverlapping();
    }

    public function getSiteStatContent(): ?array
    {
        return [
            WithdrawRequest::ENTITY_TYPE => [
                'icon' => 'ico-money-bag',
                'to'   => 'ewallet/request/browse',
            ],
            'pending_withdrawal_request' => [
                'to' => 'ewallet/request/browse?status=' . Browse::VIEW_PENDING,
            ],
        ];
    }
}
