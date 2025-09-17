<?php

namespace MetaFox\Marketplace\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\App\Models\Package;
use MetaFox\Marketplace\Contracts\ListingSupportContract;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;

class ListingSupport implements ListingSupportContract
{
    public const INVITE_TYPE_USER  = 'user';
    public const INVITE_TYPE_EMAIL = 'email';
    public const INVITE_TYPE_PHONE = 'phone';

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const APPROVED_COLOR = '#31a24a';
    public const PENDING_COLOR  = '#f4b400';
    public const REJECTED_COLOR = '#f02848';

    public function getPaymentStatus(): array
    {
        return [
            $this->getInitPaymentStatus(),
            $this->getCompletedPaymentStatus(),
            $this->getPendingPaymentStatus(),
            $this->getCanceledPaymentStatus(),
            $this->getAllPaymentStatus(),
        ];
    }

    public function getCompletedPaymentStatus(): string
    {
        return Order::STATUS_COMPLETED;
    }

    public function getPendingPaymentStatus(): string
    {
        return Order::STATUS_PENDING_PAYMENT;
    }

    public function getAllPaymentStatus(): string
    {
        return Order::STATUS_ALL;
    }

    public function getInitPaymentStatus(): string
    {
        return Order::STATUS_INIT;
    }

    public function getCanceledPaymentStatus(): string
    {
        return Order::RECURRING_STATUS_CANCELLED;
    }

    public function getMaximumTitleLength(): int
    {
        return Settings::get('marketplace.maximum_title_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
    }

    public function getMinimumTitleLength(): int
    {
        return Settings::get('marketplace.minimum_title_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
    }

    public function getInviteMethodTypes(): array
    {
        return [self::INVITE_TYPE_EMAIL, self::INVITE_TYPE_PHONE, self::INVITE_TYPE_USER];
    }

    public function getActiveStatus(): string
    {
        return self::STATUS_ACTIVE;
    }

    public function getInactiveStatus(): string
    {
        return self::STATUS_INACTIVE;
    }

    public function getInviteLinkStatus(): array
    {
        return [
            $this->getActiveStatus(),
            $this->getInactiveStatus(),
        ];
    }

    public function getListingForForm(User $context, array $attributes = []): array
    {
        return resolve(ListingRepositoryInterface::class)->getListingForForm($context, $attributes);
    }

    public function getPriceFormat(string $currency, float $price): ?string
    {
        $format = app('currency')->getPriceFormatByCurrencyId($currency, $price);

        if (null === $format) {
            return null;
        }

        return $format;
    }

    public function getUserPriceFormat(User $user, array $prices): ?string
    {
        $price = $this->getPriceByUserCurrency($user, $prices);

        if (null === $price) {
            return null;
        }

        $userCurrency = app('currency')->getUserCurrencyId($user);

        return $this->getPriceFormat($userCurrency, $price);
    }

    public function getUserPrice(User $user, array $prices): ?float
    {
        $price = $this->getPriceByUserCurrency($user, $prices);

        if (null === $price) {
            return null;
        }

        if ($price < 0) {
            return null;
        }

        return $price;
    }

    public function getPaymentGatewayParams(User $user, Listing $resource): array
    {
        $price = $this->getUserPrice($user, $resource->price);

        if (!$price) {
            $price = 0;
        }

        return [
            'payee_id' => $resource->userId(),
            'price'    => $price,
        ];
    }

    public function getUserPaymentInformation(User $user, array $prices): ?array
    {
        $userCurrency = app('currency')->getUserCurrencyId($user);

        $price = Arr::get($prices, $userCurrency);

        if (null === $price) {
            return null;
        }

        $price = (float) $price;

        if ($price < 0) {
            return null;
        }

        return [$price, $userCurrency];
    }

    public function getPriceByCurrency(string $currency, array $price): ?float
    {
        $price = Arr::get($price, $currency);

        if (null === $price) {
            return null;
        }

        return (float) $price;
    }

    protected function getPriceByUserCurrency(User $user, array $prices): ?float
    {
        $userCurrency = app('currency')->getUserCurrencyId($user);

        return $this->getPriceByCurrency($userCurrency, $prices);
    }

    public function getInviteUserType(): string
    {
        return self::INVITE_TYPE_USER;
    }

    public function getStatusLabel(string $status): ?string
    {
        return match ($status) {
            $this->getPendingPaymentStatus()   => __p('marketplace::phrase.payment_status.pending_payment'),
            $this->getInitPaymentStatus()      => __p('marketplace::phrase.payment_status.pending_action'),
            $this->getCompletedPaymentStatus() => __p('marketplace::phrase.payment_status.completed'),
            $this->getCanceledPaymentStatus()  => __p('marketplace::phrase.payment_status.canceled'),
            default                            => null
        };
    }

    public function getStatusInfo(string $status): array
    {
        return match ($status) {
            $this->getPendingPaymentStatus()   => [
                'label' => __p('marketplace::phrase.payment_status.pending_payment'),
                'color' => self::PENDING_COLOR,
            ],
            $this->getInitPaymentStatus()      => [
                'label' => __p('marketplace::phrase.payment_status.pending_action'),
                'color' => self::PENDING_COLOR,
            ],
            $this->getCompletedPaymentStatus() => [
                'label' => __p('marketplace::phrase.payment_status.completed'),
                'color' => self::APPROVED_COLOR,
            ],
            $this->getCanceledPaymentStatus()  => [
                'label' => __p('marketplace::phrase.payment_status.canceled'),
                'color' => self::REJECTED_COLOR,
            ],
            default                            => []
        };
    }

    public function getInvoiceStatusOptionForFrom(): array
    {
        return [
            [
                'label' => __p('marketplace::phrase.payment_status.all'),
                'value' => $this->getAllPaymentStatus(),
            ],
            [
                'label' => __p('marketplace::phrase.payment_status.pending_payment'),
                'value' => $this->getPendingPaymentStatus(),
            ],
            [
                'label' => __p('marketplace::phrase.payment_status.pending_action'),
                'value' => $this->getInitPaymentStatus(),
            ],
            [
                'label' => __p('marketplace::phrase.payment_status.completed'),
                'value' => $this->getCompletedPaymentStatus(),
            ],
            [
                'label' => __p('marketplace::phrase.payment_status.canceled'),
                'value' => $this->getCanceledPaymentStatus(),
            ],
        ];
    }

    public function isExpired(?Listing $listing): bool
    {
        if (null === $listing) {
            return false;
        }

        $startExpiredAt = $listing->start_expired_at;

        if (!$startExpiredAt) {
            return false;
        }

        return Carbon::parse($startExpiredAt)->lessThanOrEqualTo(Carbon::now());
    }

    public function isFree(User $user, array $prices): bool
    {
        $price = $this->getPriceByUserCurrency($user, $prices);

        if (null === $price) {
            return false;
        }

        return $price == 0;
    }

    public function getExpiredLabel(Listing $listing, bool $isListing = true): ?string
    {
        $listingExpired = $listing->start_expired_at;

        if (!$listingExpired) {
            return null;
        }

        if ($listing->notify_at) {
            return null;
        }

        $now = Carbon::now();

        $listingExpired = Carbon::parse($listingExpired);

        if ($now->greaterThanOrEqualTo($listingExpired)) {
            return null;
        }

        $remainedDays = $listingExpired->diffInDays($now);

        if ($isListing) {
            return __p('marketplace::phrase.expires_in_total_days', [
                'total' => $remainedDays ?: 1,
            ]);
        }

        if ($remainedDays >= 1) {
            return __p('marketplace::phrase.expires_in_total_days', [
                'total' => $remainedDays,
            ]);
        }

        $remainedHours = $listingExpired->diffInHours($now);

        if ($remainedHours >= 1) {
            return __p('marketplace::phrase.expires_in_total_hours', [
                'total' => $remainedHours,
            ]);
        }

        $remainedMinutes = $listingExpired->diffInMinutes($now);

        if ($remainedMinutes >= 1) {
            return __p('marketplace::phrase.expires_in_total_minutes', [
                'total' => $remainedMinutes,
            ]);
        }

        $remainedSeconds = $listingExpired->diffInSeconds($now);

        if ($remainedSeconds >= 1) {
            return __p('marketplace::phrase.expires_in_total_seconds', [
                'total' => $remainedSeconds,
            ]);
        }

        return null;
    }

    public function isActivityPointAppActive(): bool
    {
        $active = app('events')->dispatch('activitypoint.active_payment');

        if (is_bool($active)) {
            return $active;
        }

        return app_active('metafox/activity-point');
    }

    public function enableTopic(User $user, ?User $owner): bool
    {
        if (null === $owner) {
            return true;
        }

        if ($owner instanceof HasUserProfile) {
            return true;
        }

        return false;
    }

    public function getFormValues(User $context, Listing $listing): array
    {
        [$price, $currencyId] = ListingFacade::getUserPaymentInformation($context, $listing->price);

        return [
            'price'       => $price,
            'currency_id' => $currencyId,
        ];
    }

    public function isUsingMultiStepFormForEwallet(): bool
    {
        /**
         * @var Package $app
         */
        $app = resolve(\MetaFox\App\Repositories\PackageRepositoryInterface::class)->getPackageByName('metafox/emoney');

        if (version_compare($app->version, '5.1.5', '<')) {
            return false;
        }

        return true;
    }

    public function getStatusTexts(Listing $listing): array
    {
        return match ($listing->isApproved()) {
            true    => [
                'label' => __p('core::phrase.approved'),
                'color' => null,
            ],
            default => [
                'label' => __p('core::phrase.pending'),
                'color' => null,
            ]
        };
    }
}
