<?php

namespace MetaFox\Subscription\Support;

use Illuminate\Support\Arr;
use MetaFox\App\Models\Package;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Support\Payment;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;

class Helper
{
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_DELETED  = 'deleted';
    public const STATUS_DEACTIVE = 'deactive';

    public const RENEW_TYPE_MANUAL = 'manual';
    public const RENEW_TYPE_AUTO   = 'auto';

    public const RECURRING_PERIOD_MONTHLY    = 'monthly';
    public const RECURRING_PERIOD_QUARTERLY  = 'quarterly';
    public const RECURRING_PERIOD_BIANNUALLY = 'biannually';
    public const RECURRING_PERIOD_ANNUALLY   = 'annually';

    public const DEFAULT_DAY_RECURRING_FOR_NOTIFICATION = 3;

    public const MIN_TOTAL_DAY_RECURRING_FOR_NOTIFICATION = 1;

    public const DEFAULT_BACKGROUND_COLOR = '#ebf1f5';

    public const DEPENDENCY_UPGRADE   = 'upgrade';
    public const DEPENDENCY_DOWNGRADE = 'downgrade';

    public const ACTIVE_RECURRING_PACKAGE_CACHE_ID = 'subscription_active_recurring_packages';
    public const ACTIVE_PACKAGE_CACHE_ID           = 'subscription_active_packages';
    public const ALL_PACKAGE_CACHE_ID              = 'subscription_packages';
    public const FEATURE_PACKAGE_CACHE_ID          = 'features_package_%d';

    public const ALL_CUSTOM_REASONS_CACHE_ID = 'custom_subscription_reasons';
    public const ALL_ACTIVE_REASONS_CACHE_ID = 'active_subscription_reasons';
    public const ALL_REASONS_CACHE_ID        = 'subscription_reasons';

    public const ALL_TRANSACTION_CACHE_ID = 'subscription_transactions';

    public const DEFAULT_CACHE_TTL = null;

    public const VIEW_ADMINCP = 'admincp';
    public const VIEW_FILTER  = 'filter';

    public const PERMISSION_CAN_MARK_AS_POPULAR   = 'can_mark_as_popular';
    public const PERMISSION_CAN_PURCHASE          = 'can_purchase';
    public const PERMISSION_CAN_ACTIVE            = 'can_active';
    public const PERMISSION_CAN_VIEW_SUBSCRIPTION = 'can_view_subscription';

    public const PACKAGE_TYPE_ONE_TIME  = 'one_time';
    public const PACKAGE_TYPE_RECURRING = 'recurring';

    public const DELETE_REASON_DEFAULT = 1;
    public const DELETE_REASON_CUSTOM  = 2;

    public const COMPARISON_TYPE_YES  = 'yes';
    public const COMPARISON_TYPE_NO   = 'no';
    public const COMPARISON_TYPE_TEXT = 'text';

    public const MAX_LENGTH_FOR_COMPARISON_TEXT = 200;

    public const COMPARISON_CACHE_ID = 'subscription_comparisons';

    public const MODULE_NAME = 'subscription';

    public const COMPARISON_RESOURCE_FILTER_NAME = 'subscription_comparison';

    public const COMPARISON_RESOURCE_ADMINCP_NAME = 'subscription_comparison_admincp';

    public const PAYMENT_STATUS_EXPIRED = 'expired';

    public const ACTION_PLUS     = 'plus';
    public const ACTION_SUBTRACT = 'subtract';

    public const UPGRADE_FORM_ACTION = 'upgrade';
    public const PAY_NOW_FORM_ACTION = 'pay_now';

    public const DEFAULT_EXPIRED_ADDON_DAY = 3;

    public const MAX_PACKAGE_TITLE_LENGTH       = 50;
    public const MAX_PACKAGE_DESCRIPTION_LENGTH = 255;

    public const MAX_COMPARISON_TITLE_LENGTH = 100;

    public const STATISTICS_ALL    = 'all';
    public const STATISTICS_CUSTOM = 'custom';

    public const CANCELED_URL_LOCATION_HOME           = 'home';
    public const CANCELED_URL_LOCATION_INVOICE_DETAIL = 'invoice_detail';

    public static function getStatisticOptions(): array
    {
        return [
            [
                'label' => __p('subscription::admin.all_time'),
                'value' => self::STATISTICS_ALL,
            ],
            [
                'label' => __p('subscription::admin.custom_time'),
                'value' => self::STATISTICS_CUSTOM,
            ],
        ];
    }

    public static function getComparisonTypes(): array
    {
        return [self::COMPARISON_TYPE_YES, self::COMPARISON_TYPE_NO, self::COMPARISON_TYPE_TEXT];
    }

    public static function getUpgradeType(): array
    {
        return [self::UPGRADE_FORM_ACTION, self::PAY_NOW_FORM_ACTION];
    }

    public static function getItemStatus(): array
    {
        return [self::STATUS_ACTIVE, self::STATUS_DELETED, self::STATUS_DEACTIVE];
    }

    public static function getPaymentStatusForSearching(): array
    {
        return [
            self::getExpiredPaymentStatus(), self::getCompletedPaymentStatus(), self::getPendingPaymentStatus(),
            self::getCanceledPaymentStatus(),
        ];
    }

    public static function getPaymentStatus(): array
    {
        return [
            self::getInitPaymentStatus(), self::getExpiredPaymentStatus(), self::getCompletedPaymentStatus(),
            self::getPendingPaymentStatus(), self::getCanceledPaymentStatus(),
        ];
    }

    public static function getPaymentTypRecurring(): string
    {
        return Payment::PAYMENT_RECURRING;
    }

    public static function getPaymentTypeOnetime(): string
    {
        return Payment::PAYMENT_ONETIME;
    }

    public static function getPaymentType(): array
    {
        return [Payment::PAYMENT_ONETIME, Payment::PAYMENT_RECURRING];
    }

    public static function getRenewType(): array
    {
        return [self::RENEW_TYPE_AUTO, self::RENEW_TYPE_MANUAL];
    }

    public static function getRecurringPeriodType(): array
    {
        return [
            self::RECURRING_PERIOD_ANNUALLY, self::RECURRING_PERIOD_BIANNUALLY, self::RECURRING_PERIOD_MONTHLY,
            self::RECURRING_PERIOD_QUARTERLY,
        ];
    }

    public static function getRecurringPeriodDays(?string $type): int
    {
        return match ($type) {
            self::RECURRING_PERIOD_QUARTERLY  => 90,
            self::RECURRING_PERIOD_BIANNUALLY => 180,
            self::RECURRING_PERIOD_ANNUALLY   => 365,
            // self::RECURRING_PERIOD_MONTHLY    => 30,
            default                           => 30
        };
    }

    public static function getDisallowedRolesForSuccess(): array
    {
        return [
            UserRole::BANNED_USER_ID,
            UserRole::PAGE_USER_ID,
            UserRole::GUEST_USER_ID,
            UserRole::SUPER_ADMIN_USER_ID,
        ];
    }

    public static function getDisallowedRolesForVisibility(): array
    {
        return [
            UserRole::ADMIN_USER_ID,
            UserRole::PAGE_USER_ID,
            UserRole::SUPER_ADMIN_USER_ID,
            UserRole::BANNED_USER_ID,
        ];
    }

    public static function getDisallowedRolesForDowngrade(): array
    {
        return [
            UserRole::ADMIN_USER_ID,
            UserRole::PAGE_USER_ID,
            UserRole::SUPER_ADMIN_USER_ID,
        ];
    }

    public static function getAllowedRenewMethod(): array
    {
        return [
            [
                'label'       => __p('subscription::admin.auto_renew'),
                'value'       => self::RENEW_TYPE_AUTO,
                'description' => __p('subscription::phrase.you_want_it_to_be_renewed_automatically'),
            ],
            [
                'label'       => __p('subscription::admin.manual_renew'),
                'value'       => self::RENEW_TYPE_MANUAL,
                'description' => __p('subscription::phrase.you_want_to_pay_the_renewal_fee_yourself'),
            ],
        ];
    }

    public static function getRecurringPeriods($byKey = false): array
    {
        $periods = [
            self::RECURRING_PERIOD_MONTHLY    => [
                'label' => __p('subscription::admin.month'),
                'value' => self::RECURRING_PERIOD_MONTHLY,
            ],
            self::RECURRING_PERIOD_QUARTERLY  => [
                'label' => __p('subscription::admin.quarter'),
                'value' => self::RECURRING_PERIOD_QUARTERLY,
            ],
            self::RECURRING_PERIOD_BIANNUALLY => [
                'label' => __p('subscription::admin.biannual'),
                'value' => self::RECURRING_PERIOD_BIANNUALLY,
            ],
            self::RECURRING_PERIOD_ANNUALLY   => [
                'label' => __p('subscription::admin.annual'),
                'value' => self::RECURRING_PERIOD_ANNUALLY,
            ],
        ];

        if (!$byKey) {
            $periods = array_values($periods);
        }

        return $periods;
    }

    public static function getPackageImageSizes(): array
    {
        return [240];
    }

    public static function getPeriodLabel(string $period): ?string
    {
        $periods = self::getRecurringPeriods(true);

        if (!Arr::has($periods, $period)) {
            return null;
        }

        return Arr::get($periods, $period . '.label');
    }

    public static function getDependencyTypes(): array
    {
        return [self::DEPENDENCY_UPGRADE, self::DEPENDENCY_DOWNGRADE];
    }

    public static function handleTitleForView(?string $title): ?string
    {
        if (null === $title) {
            return null;
        }

        $title = ban_word()->clean($title);

        $title = ban_word()->parse($title);

        return $title;
    }

    public static function getItemView(bool $isAdminCP = false): array
    {
        $views = match ($isAdminCP) {
            true  => [self::VIEW_ADMINCP, Browse::VIEW_SEARCH],
            false => [self::VIEW_FILTER, Browse::VIEW_SEARCH]
        };

        return $views;
    }

    public static function getItemType(): array
    {
        return [self::PACKAGE_TYPE_ONE_TIME, self::PACKAGE_TYPE_RECURRING];
    }

    public static function isActive(?string $status): bool
    {
        return $status == self::STATUS_ACTIVE;
    }

    public static function isDeactive(?string $status): bool
    {
        return $status == self::STATUS_DEACTIVE;
    }

    public static function isDeleted(?string $status): bool
    {
        return $status == self::STATUS_DELETED;
    }

    public static function getCompletedPaymentStatus(): string
    {
        return Order::STATUS_COMPLETED;
    }

    public static function getCanceledPaymentStatus(): string
    {
        return Order::RECURRING_STATUS_CANCELLED;
    }

    public static function getExpiredPaymentStatus(): string
    {
        return self::PAYMENT_STATUS_EXPIRED;
    }

    public static function getPendingPaymentStatus(): string
    {
        return Order::STATUS_PENDING_PAYMENT;
    }

    public static function getInitPaymentStatus(): string
    {
        return Order::STATUS_INIT;
    }

    public static function getRecurringPriceLabel(float $price, string $currency, string $period): string
    {
        $recurringPrice = app('currency')->getPriceFormatByCurrencyId($currency, $price);

        $period = self::getPeriodLabel($period);

        return __p('subscription::phrase.recurring_price_info', [
            'price'  => $recurringPrice,
            'period' => $period,
        ]);
    }

    public static function getTransactionPaymentStatus(string $status): string
    {
        return match ($status) {
            self::getCompletedPaymentStatus() => __p('subscription::phrase.paid'),
            self::getCanceledPaymentStatus()  => __p('subscription::phrase.payment_status.cancelled'),
        };
    }

    public static function getPaymentStatusLabel(string $status): string
    {
        return match ($status) {
            self::getCompletedPaymentStatus() => __p('subscription::phrase.payment_status.active'),
            self::getCanceledPaymentStatus()  => __p('subscription::phrase.payment_status.cancelled'),
            self::getExpiredPaymentStatus()   => __p('subscription::phrase.payment_status.expired'),
            self::getPendingPaymentStatus()   => __p('subscription::phrase.payment_status.pending_payment'),
            self::getInitPaymentStatus()      => __p('subscription::phrase.payment_status.pending_action'),
        };
    }

    public static function checkAllowedStatusForUpdateAdminCP(string $fromStatus, string $toStatus): bool
    {
        $options = [
            self::getPendingPaymentStatus()   => [self::getCompletedPaymentStatus()],
            self::getCompletedPaymentStatus() => [self::getCanceledPaymentStatus()],
            self::getCanceledPaymentStatus()  => [self::getCompletedPaymentStatus()],
        ];

        return Arr::has($options, $fromStatus) && in_array($toStatus, Arr::get($options, $fromStatus));
    }

    public static function getFeaturePackageCacheId(int $packageId): string
    {
        return sprintf(self::FEATURE_PACKAGE_CACHE_ID, $packageId);
    }

    public static function isUsingMultiStepFormForEwallet(): bool
    {
        /**
         * @var Package $app
         */
        $app = resolve(PackageRepositoryInterface::class)->getPackageByName('metafox/emoney');

        if (version_compare($app->version, '5.1.5', '<')) {
            return false;
        }

        return true;
    }
}
