<?php

namespace MetaFox\Advertise\Support;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Contracts\AdvertisePaymentInterface;
use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Models\Invoice;
use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Advertise\Repositories\PlacementRepositoryInterface;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Advertise\Support\Contracts\SupportInterface;
use MetaFox\App\Models\Package;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Localize\Repositories\LanguageRepositoryInterface;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;

class Support implements SupportInterface
{
    public const PLACEMENT_PPC = 'ppc';
    public const PLACEMENT_CPM = 'cpm';

    public const DELETE_PERMANENTLY = 'permanently';
    public const DELETE_MIGRATION   = 'migration';

    public const ADVERTISE_HTML  = 'html';
    public const ADVERTISE_IMAGE = 'image';

    public const MAX_HTML_TITLE_LENGTH       = 25;
    public const MAX_HTML_DESCRIPTION_LENGTH = 135;

    public const ADVERTISE_STATUS_UNPAID    = 'unpaid';
    public const ADVERTISE_STATUS_PENDING   = 'pending';
    public const ADVERTISE_STATUS_APPROVED  = 'approved';
    public const ADVERTISE_STATUS_DENIED    = 'denied';
    public const ADVERTISE_STATUS_COMPLETED = 'completed';
    /**
     * Do not use below statuses to insert with Advertise.
     */
    public const ADVERTISE_STATUS_RUNNING  = 'running';
    public const ADVERTISE_STATUS_UPCOMING = 'upcoming';
    public const ADVERTISE_STATUS_ENDED    = 'ended';

    public const UNLIMITED_TOTAL = 0;

    public const LOCATION_SIDE     = 'side';
    public const LOCATION_SUB_SIDE = 'subside';
    public const LOCATION_TOP      = 'top';
    public const LOCATION_MAIN     = 'main';
    public const LOCATION_HEADER   = 'header';
    public const LOCATION_BOTTOM   = 'bottom';
    public const LOCATION_FOOTER   = 'footer';

    public const TYPE_CLICK      = 'click';
    public const TYPE_IMPRESSION = 'impression';

    public const DATE_TYPE_HOUR = 'hour';

    public const STATISTIC_VIEW_DAY   = 'day';
    public const STATISTIC_VIEW_WEEK  = 'week';
    public const STATISTIC_VIEW_MONTH = 'month';

    public const PAID_COLOR            = '#31a24a';
    public const UNPAID_COLOR          = '#f4b400';
    public const PENDING_PAYMENT_COLOR = '#4a97dc';
    public const CANCELLED_COLOR       = '#f02848';

    public const PRICE_SETTING_NAME = 'purchase_sponsor_price';

    public const SPONSOR_TYPE_ITEM = 'item';
    public const SPONSOR_TYPE_FEED = 'feed';

    public const MAX_TOTAL_NUMBER = 100000000;

    public function __construct(protected PlacementRepositoryInterface $placementRepository, protected SponsorRepositoryInterface $sponsorRepository)
    {
    }

    public function getSponsorTypeOptions(): array
    {
        return [
            [
                'label' => __p('advertise::phrase.feed'),
                'value' => self::SPONSOR_TYPE_FEED,
            ],
            [
                'label' => __p('advertise::phrase.item'),
                'value' => self::SPONSOR_TYPE_ITEM,
            ],
        ];
    }

    public function getAllowSponsorTypes(): array
    {
        return Arr::pluck(self::getSponsorTypeOptions(), 'value');
    }

    public function getPlacementTypes(): array
    {
        return [
            [
                'label' => __p('advertise::phrase.ppc'),
                'value' => self::PLACEMENT_PPC,
            ],
            [
                'label' => __p('advertise::phrase.cpm'),
                'value' => self::PLACEMENT_CPM,
            ],
        ];
    }

    public function getPendingActionStatus(): string
    {
        return Order::STATUS_INIT;
    }

    public function getDisallowedUserRoleOptions(): array
    {
        return [UserRole::BANNED_USER_ID];
    }

    public function getUserRoleOptions(): array
    {
        $repository = resolve(RoleRepositoryInterface::class);

        $options = $repository->getRoleOptions();

        if (null === $options) {
            return [];
        }

        $disallowedOptions = $this->getDisallowedUserRoleOptions();

        $options = array_filter($options, function ($option) use ($disallowedOptions) {
            return !in_array($option['value'], $disallowedOptions);
        });

        return $options;
    }

    public function getDeleteOptions(): array
    {
        return [
            [
                'label' => __p('advertise::phrase.delete_all_items'),
                'value' => self::DELETE_PERMANENTLY,
            ],
            [
                'label' => __p('advertise::phrase.delete_move_all_items'),
                'value' => self::DELETE_MIGRATION,
            ],
        ];
    }

    public function getAdvertiseTypes(): array
    {
        return [
            [
                'label' => __p('advertise::phrase.image'),
                'value' => self::ADVERTISE_IMAGE,
            ],
            [
                'label' => __p('advertise::phrase.html'),
                'value' => self::ADVERTISE_HTML,
            ],
        ];
    }

    public function getPlacementOptions(User $context, bool $isFree = false, ?string $currencyId = null, ?bool $isActive = true): array
    {
        $placements = $this->placementRepository->getPlacementsForAdvertise($context, $isFree, $isActive);

        if (!$placements->count()) {
            return [];
        }

        return $placements->map(function ($placement) use ($currencyId) {
            $data = [
                'label'          => $placement->toTitle(),
                'value'          => $placement->entityId(),
                'placement_type' => $placement->placement_type,
                'description'    => $placement?->placementText->text_parsed,
            ];

            if (null !== $currencyId) {
                Arr::set($data, 'price', is_array($placement->price) ? Arr::get($placement->price, $currencyId) : null);
            }

            return $data;
        })->toArray();
    }

    public function getGenderOptions(): array
    {
        return resolve(UserGenderRepositoryInterface::class)->getGenderOptions();
    }

    public function getLanguageOptions(): array
    {
        return resolve(LanguageRepositoryInterface::class)->getActiveLanguages()
            ->map(function ($language) {
                return ['value' => $language->language_code, 'label' => $language->name];
            })
            ->toArray();
    }

    public function getCancelledPaymentStatus(): string
    {
        return Order::RECURRING_STATUS_CANCELLED;
    }

    public function getCompletedPaymentStatus(): string
    {
        return Order::STATUS_COMPLETED;
    }

    public function getPendingPaymentStatus(): string
    {
        return Order::STATUS_PENDING_PAYMENT;
    }

    public function getAdvertiseStatusOptions(): array
    {
        return [
            [
                'label' => __p('advertise::phrase.status.upcoming'),
                'value' => self::ADVERTISE_STATUS_UPCOMING,
            ],
            [
                'label' => __p('advertise::phrase.status.running'),
                'value' => self::ADVERTISE_STATUS_RUNNING,
            ],
            [
                'label' => __p('advertise::phrase.status.completed'),
                'value' => self::ADVERTISE_STATUS_COMPLETED,
            ],
            [
                'label' => __p('advertise::phrase.status.ended'),
                'value' => self::ADVERTISE_STATUS_ENDED,
            ],
            [
                'label' => __p('advertise::phrase.status.pending'),
                'value' => self::ADVERTISE_STATUS_PENDING,
            ],
            [
                'label' => __p('advertise::phrase.status.unpaid'),
                'value' => self::ADVERTISE_STATUS_UNPAID,
            ],
            [
                'label' => __p('advertise::phrase.status.denied'),
                'value' => self::ADVERTISE_STATUS_DENIED,
            ],
        ];
    }

    public function getActiveOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.is_active'),
                'value' => 1,
            ],
            [
                'label' => __p('advertise::phrase.inactive'),
                'value' => 0,
            ],
        ];
    }

    public function getAllowedViews(): array
    {
        return [Browse::VIEW_MY];
    }

    public function isSponsorChangePrice(Sponsor $sponsor): bool
    {
        if (null === $sponsor->latestUnpaidInvoice) {
            return true;
        }

        $item = $sponsor->item;

        if (!$item instanceof Content) {
            return true;
        }

        $user = $sponsor->user;

        if (null === $user) {
            return true;
        }

        $price = resolve(SponsorSettingServiceInterface::class)->getPriceForPayment($user, $item, $sponsor->latestUnpaidInvoice->currency_id);

        if (null === $price) {
            return true;
        }

        $price = $this->calculateSponsorPrice($sponsor, $price);

        return $price != (float) $sponsor->latestUnpaidInvoice->price;
    }

    public function isAdvertiseChangePrice(Advertise $advertise): bool
    {
        if (null === $advertise->latestUnpaidInvoice) {
            return true;
        }

        if (null === $advertise->placement) {
            return true;
        }

        if (!is_array($advertise->placement->price)) {
            return true;
        }

        if (!Arr::has($advertise->placement->price, $advertise->latestUnpaidInvoice->currency_id)) {
            return true;
        }

        if (!is_numeric(Arr::get($advertise->placement->price, $advertise->latestUnpaidInvoice->currency_id))) {
            return true;
        }

        $total = match ($advertise->advertise_type) {
            self::PLACEMENT_PPC => $advertise->total_click,
            self::PLACEMENT_CPM => $advertise->total_impression,
            default             => null,
        };

        if (!is_numeric($total)) {
            return true;
        }

        $placementPrice = Arr::get($advertise->placement->price, $advertise->latestUnpaidInvoice->currency_id);

        $price = $this->calculateAdvertisePrice($advertise, $placementPrice);

        return $price != (float) $advertise->latestUnpaidInvoice->price;
    }

    public function getPlacementPriceByCurrencyId(int $placementId, string $currencyId): ?float
    {
        return $this->placementRepository->getPlacementPriceByCurrencyId($placementId, $currencyId);
    }

    public function getAvailablePlacements(User $user, ?bool $isActive = true): array
    {
        return $this->placementRepository->getAvailablePlacements($user, $isActive);
    }

    public function calculateSponsorPrice(Sponsor $sponsor, float $price): float
    {
        return round(($sponsor->total_impression * $price) / 1000, 2);
    }

    public function calculateAdvertisePrice(Advertise $advertise, ?float $placementPrice): ?float
    {
        return match ($advertise->advertise_type) {
            self::PLACEMENT_PPC => round($advertise->total_click * $placementPrice, 2),
            self::PLACEMENT_CPM => round(($advertise->total_impression * $placementPrice) / 1000, 2),
            default             => null,
        };
    }

    public function getInvoiceStatuses(): array
    {
        return [$this->getPendingActionStatus(), $this->getPendingPaymentStatus(), $this->getCompletedPaymentStatus(), $this->getCancelledPaymentStatus()];
    }

    public function getAdvertiseStatuses(): array
    {
        return [
            self::ADVERTISE_STATUS_UNPAID,
            self::ADVERTISE_STATUS_PENDING,
            self::ADVERTISE_STATUS_APPROVED,
            self::ADVERTISE_STATUS_DENIED,
            self::ADVERTISE_STATUS_COMPLETED,
            self::ADVERTISE_STATUS_RUNNING,
            self::ADVERTISE_STATUS_UPCOMING,
            self::ADVERTISE_STATUS_ENDED,
        ];
    }

    public function getInvoiceStatusOptions(): array
    {
        return [
            [
                'label' => __p('advertise::phrase.payment_status.unpaid'),
                'value' => $this->getPendingActionStatus(),
            ],
            [
                'label' => __p('advertise::phrase.payment_status.pending_payment'),
                'value' => $this->getPendingPaymentStatus(),
            ],
            [
                'label' => __p('advertise::phrase.payment_status.cancelled'),
                'value' => $this->getCancelledPaymentStatus(),
            ],
            [
                'label' => __p('advertise::phrase.payment_status.paid'),
                'value' => $this->getCompletedPaymentStatus(),
            ],
        ];
    }

    public function getAllowedLocations(): array
    {
        return [
            self::LOCATION_BOTTOM,
            self::LOCATION_FOOTER,
            self::LOCATION_HEADER,
            self::LOCATION_MAIN,
            self::LOCATION_SIDE,
            self::LOCATION_SUB_SIDE,
            self::LOCATION_TOP,
        ];
    }

    public function getAmount(Advertise $advertise): ?int
    {
        return match ($advertise->advertise_type) {
            self::PLACEMENT_CPM => $advertise->total_impression,
            self::PLACEMENT_PPC => $advertise->total_click,
            default             => null,
        };
    }

    public function getCurrentAmount(Advertise $advertise): ?int
    {
        if (null === $advertise->statistic) {
            return null;
        }

        return match ($advertise->advertise_type) {
            self::PLACEMENT_CPM => $advertise->statistic->total_impression,
            self::PLACEMENT_PPC => $advertise->statistic->total_click,
            default             => null,
        };
    }

    public function getInvoiceStatusColors(): array
    {
        return [
            self::getCompletedPaymentStatus() => [
                'label' => __p('advertise::phrase.payment_status.paid'),
                'color' => self::PAID_COLOR,
            ],
            self::getCancelledPaymentStatus() => [
                'label' => __p('advertise::phrase.payment_status.cancelled'),
                'color' => self::CANCELLED_COLOR,
            ],
            self::getPendingActionStatus() => [
                'label' => __p('advertise::phrase.payment_status.unpaid'),
                'color' => self::UNPAID_COLOR,
            ],
            self::getPendingPaymentStatus() => [
                'label' => __p('advertise::phrase.payment_status.pending_payment'),
                'color' => self::PENDING_PAYMENT_COLOR,
            ],
        ];
    }

    public function getInvoiceStatusInfo(string $status): ?array
    {
        $infos = $this->getInvoiceStatusColors();

        return Arr::get($infos, $status);
    }

    public function getAdvertiseStatusInfo(string $status): ?array
    {
        $infos = $this->getAdvertiseStatusColors();

        return Arr::get($infos, $status);
    }

    public function getAdvertiseStatusColors(): array
    {
        return [
            self::ADVERTISE_STATUS_UNPAID => [
                'label' => __p('advertise::phrase.status.unpaid'),
                'color' => self::UNPAID_COLOR,
            ],
            self::ADVERTISE_STATUS_PENDING => [
                'label' => __p('advertise::phrase.status.pending'),
                'color' => self::PENDING_PAYMENT_COLOR,
            ],
            self::ADVERTISE_STATUS_DENIED => [
                'label' => __p('advertise::phrase.status.denied'),
                'color' => self::CANCELLED_COLOR,
            ],
        ];
    }

    public function getActivePlacementsForSetting(): array
    {
        return $this->placementRepository->getActivePlacementsForSetting();
    }

    public function getCurrentSponsorPrice(Sponsor $sponsor): ?float
    {
        $item = $sponsor->item;

        $user = $sponsor->user;

        if (!$item instanceof Content) {
            return null;
        }

        if (!$user instanceof User) {
            return null;
        }

        $currencyId = app('currency')->getUserCurrencyId($user);

        $price = resolve(SponsorSettingServiceInterface::class)->getPriceForPayment($user, $item, $currencyId);

        if (null === $price) {
            return null;
        }

        return (float) $price;
    }

    public function isPendingSponsor(Content $content): bool
    {
        return $this->sponsorRepository->isPendingSponsor($content);
    }

    public function isApprovedSponsor(Content $content): bool
    {
        return $this->sponsorRepository->isApprovedSponsor($content);
    }

    public function roundPriceUp(array $prices): array
    {
        if (!count($prices)) {
            return [];
        }

        foreach ($prices as $key => $price) {
            if (!is_numeric($price)) {
                continue;
            }

            $prices[$key] = round($price, 2);
        }

        return $prices;
    }

    public function isUsingMultiStepFormForEwallet(): bool
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

    public function getMorphedModel(int $itemId, string $itemType): ?AdvertisePaymentInterface
    {
        $morphedModel = Relation::getMorphedModel($itemType);

        if (null === $morphedModel) {
            return null;
        }

        $model = (new $morphedModel())->newQuery()
            ->where('id', '=', $itemId)
            ->first();

        if (null === $model) {
            return null;
        }

        return $model;
    }

    public function isFreeSponsorInvoice(Sponsor $sponsor): bool
    {
        if (!$sponsor->paidInvoice instanceof Invoice) {
            return false;
        }

        if ($sponsor->paidInvoice->price != 0) {
            return false;
        }

        return true;
    }
}
