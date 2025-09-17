<?php

namespace MetaFox\Featured\Support;

use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\App\Models\Package;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Featured\Contracts\SupportInterface;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Repositories\InvoiceRepositoryInterface;
use MetaFox\Featured\Repositories\ItemRepositoryInterface;
use MetaFox\Featured\Repositories\PackageRepositoryInterface;
use MetaFox\Featured\Repositories\TransactionRepositoryInterface;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Payment\Models\Order;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\UserRole;
use MetaFox\Platform\Contracts\Content;

class Support implements SupportInterface
{
    public const APPLICABLE_ITEM_TYPE_CACHE_ID = 'featured_applicable_item_types';

    public const ALLOWED_ITEM_TYPE_CACHE_ID = 'featured_allowed_item_types';

    public function __construct(protected PackageRepositoryInterface $packageRepository, protected ItemRepositoryInterface $itemRepository, protected InvoiceRepositoryInterface $invoiceRepository, protected TransactionRepositoryInterface $transactionRepository)
    {
    }

    public function getDurationOptions(): array
    {
        return [
            [
                'label' => __p('featured::phrase.duration_day'),
                'value' => Constants::DURATION_DAY,
            ],
            [
                'label' => __p('featured::phrase.duration_month'),
                'value' => Constants::DURATION_MONTH,
            ],
            [
                'label' => __p('featured::phrase.duration_year'),
                'value' => Constants::DURATION_YEAR,
            ],
        ];
    }

    public function getCurrencyOptions(?string $userCurrency = null): array
    {
        $currencies = app('currency')->getActiveOptions();

        if (!is_array($currencies) || !count($currencies)) {
            return [];
        }

        if (null === $userCurrency) {
            return $currencies;
        }

        uasort($currencies, function ($a, $b) use ($userCurrency) {
            $aCurrency = Arr::get($a, 'value');

            $bCurrency = Arr::get($b, 'value');

            if ($aCurrency === $userCurrency) {
                return -1;
            }

            if ($bCurrency === $userCurrency) {
                return 1;
            }

            return 0;
        });

        return array_values($currencies);
    }

    public function getApplicableItemTypeOptions(): array
    {
        return localCacheStore()->rememberForever(self::APPLICABLE_ITEM_TYPE_CACHE_ID, function () {
            return Permission::query()
                ->join('packages', function (JoinClause $joinClause) {
                    $joinClause->on('auth_permissions.module_id', '=', 'packages.alias')
                        ->where('packages.is_installed', '=', 1)
                        ->where('packages.is_active', '=', 1);
                })
                ->where([
                    'auth_permissions.action'      => 'feature',
                    'auth_permissions.is_editable' => 1,
                ])
                ->orderBy('auth_permissions.entity_type')
                ->get(['auth_permissions.*'])
                ->map(function (Permission $permission) {
                    return [
                        'label'           => $this->getEntityTypeLabel($permission->module_id, $permission->entity_type),
                        'value'           => $permission->entity_type,
                        'module_id'       => $permission->module_id,
                        'permission_name' => $permission->name,
                    ];
                })
                ->toArray();
        });
    }

    public function getApplicableRoleOptions(): array
    {
        $roles = resolve(RoleRepositoryInterface::class)->getUsableRoles()
            ->map(function (Role $role) {
                return [
                    'label' => $role->name,
                    'value' => $role->entityId(),
                ];
            })
            ->toArray();

        $roles = array_filter($roles, function ($role) {
            return Arr::get($role, 'value') != UserRole::SUPER_ADMIN_USER_ID;
        });

        return array_values($roles);
    }

    public function getDurationOptionsForSearch(): array
    {
        return array_merge([
            [
                'label' => __p('featured::admin.endless'),
                'value' => Constants::DURATION_ENDLESS,
            ],
        ], $this->getDurationOptions());
    }

    public function getPricingOptions(): array
    {
        return [
            [
                'label' => __p('core::web.free'),
                'value' => Constants::PRICING_OPTION_FREE,
            ],
            [
                'label' => __p('featured::admin.charged'),
                'value' => Constants::PRICING_OPTION_CHARGED,
            ],
        ];
    }

    public function getStatusOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.is_active'),
                'value' => Constants::STATUS_OPTION_ACTIVE,
            ],
            [
                'label' => __p('core::phrase.inactive'),
                'value' => Constants::STATUS_OPTION_INACTIVE,
            ],
        ];
    }

    public function getDurationText(?string $durationPeriod, ?int $durationValue): string
    {
        if (null === $durationPeriod || null === $durationValue) {
            return __p('featured::admin.endless');
        }

        return match ($durationPeriod) {
            Constants::DURATION_MONTH => __p('featured::admin.duration_month_with_number', ['number' => $durationValue]),
            Constants::DURATION_YEAR  => __p('featured::admin.duration_year_with_number', ['number' => $durationValue]),
            default                   => __p('featured::admin.duration_day_with_number', ['number' => $durationValue]),
        };
    }

    public function getEntityTypeLabel(string $moduleId, string $entityType): string
    {
        $key = sprintf('%s::phrase.feature_setting_%s', $moduleId, $entityType);

        $label = __p($key);

        if ($label !== $key) {
            return $label;
        }

        return __p_type_key($entityType);
    }

    public function isAllowedEntityType(string $entityType): bool
    {
        $entityTypes = $this->getAllowedEntityTypes();

        return Arr::has($entityTypes, $entityType);
    }

    public function getAllowedEntityTypes(): array
    {
        return localCacheStore()->rememberForever(self::ALLOWED_ITEM_TYPE_CACHE_ID, function () {
            $entityTypes = $this->getApplicableItemTypeOptions();

            return collect($entityTypes)
                ->keyBy('value')
                ->toArray();
        });
    }

    public function morphItemFromEntityType(string $entityType, int $entityId): Content
    {
        $morphedClass = Relation::getMorphedModel($entityType);

        if (null === $morphedClass) {
            throw new AuthorizationException();
        }

        $instance = new $morphedClass();

        if (!$instance instanceof Content) {
            throw new AuthorizationException();
        }

        return $instance->newQuery()
            ->where($instance->getKeyName(), '=', $entityId)
            ->firstOrFail();
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

    public function getCancelledPaymentStatus(): string
    {
        return Constants::FEATURED_ITEM_STATUS_CANCELLED;
    }

    public function getInitPaymentStatus(): string
    {
        return Order::STATUS_INIT;
    }

    public function activateItemFeatured(?Content $item): void
    {
        if (null === $item) {
            return;
        }

        $item->activateFeature();
    }

    public function deactivateItemFeatured(?Content $item): void
    {
        if (null === $item) {
            return;
        }

        $item->deactivateFeature();
    }

    public function increasePackageTotalActive(?int $packageId): void
    {
        if (null === $packageId) {
            return;
        }

        $this->packageRepository->increasePackageTotalActive($packageId);
    }

    public function increasePackageTotalEnd(?int $packageId): void
    {
        if (null === $packageId) {
            return;
        }

        $this->packageRepository->increasePackageTotalEnd($packageId);
    }

    public function increasePackageTotalCancelled(?int $packageId): void
    {
        if (null === $packageId) {
            return;
        }

        $this->packageRepository->increasePackageTotalCancelled($packageId);
    }

    public function decreasePackageTotalActive(?int $packageId): void
    {
        if (null === $packageId) {
            return;
        }

        $this->packageRepository->decreasePackageTotalActive($packageId);
    }

    public function decreasePackageTotalEnd(?int $packageId): void
    {
        if (null === $packageId) {
            return;
        }

        $this->packageRepository->decreasePackageTotalEnd($packageId);
    }

    public function decreasePackageTotalCancelled(?int $packageId): void
    {
        if (null === $packageId) {
            return;
        }

        $this->packageRepository->decreasePackageTotalCancelled($packageId);
    }

    public function hasAvailablePackages(User $user, string $entityType): bool
    {
        $options = $this->packageRepository->getPackageOptionsForEntityType($user, $entityType);

        return count($options) > 0;
    }

    public function getExpiredDatetimeByDuration(?string $durationPeriod, ?int $durationValue): ?CarbonInterface
    {
        if (null === $durationPeriod || null === $durationValue) {
            return null;
        }

        $now = Carbon::now();

        return match ($durationPeriod) {
            Constants::DURATION_DAY   => $now->addDays($durationValue),
            Constants::DURATION_MONTH => $now->addMonths($durationValue),
            Constants::DURATION_YEAR  => $now->addYears($durationValue),
            default                   => null,
        };
    }

    public function markFeaturedItemEnded(Item $item): void
    {
        $this->itemRepository->markItemEnded($item);
    }

    public function handleContentDeleted(Content $content): void
    {
        $this->itemRepository->handleContentDeleted($content);
        $this->invoiceRepository->handleContentDeleted($content);
        $this->transactionRepository->handleContentDeleted($content);
    }

    public function handleItemDeleted(Item $item): void
    {
        match ($item->status) {
            Constants::FEATURED_ITEM_STATUS_RUNNING   => Feature::decreasePackageTotalActive($item->package_id),
            Constants::FEATURED_ITEM_STATUS_CANCELLED => Feature::decreasePackageTotalCancelled($item->package_id),
            Constants::FEATURED_ITEM_STATUS_ENDED     => Feature::decreasePackageTotalEnd($item->package_id),
            default                                   => null,
        };

        if ($item->is_running) {
            Feature::deactivateItemFeatured($item->item);
        }

        $this->invoiceRepository->handleItemDeleted($item);

        $this->transactionRepository->handleItemDeleted($item);

        app('events')->dispatch('notification.delete_mass_notification_by_item', [$item], true);
    }

    public function isContentAvailableForFeature(Content $content): bool
    {
        return $this->itemRepository->isContentAvailableForFeature($content);
    }

    public function isFeaturedByUser(User $user, Content $content): bool
    {
        return $this->itemRepository->isFeaturedByUser($user, $content);
    }

    public function getSearchInvoiceStatusOptions(): array
    {
        return [
            [
                'label' => __p('featured::phrase.invoice_status.init'),
                'value' => $this->getInitPaymentStatus(),
            ],
            [
                'label' => __p('featured::phrase.invoice_status.pending_payment'),
                'value' => $this->getPendingPaymentStatus(),
            ],
            [
                'label' => __p('featured::phrase.invoice_status.completed'),
                'value' => $this->getCompletedPaymentStatus(),
            ],
            [
                'label' => __p('featured::phrase.invoice_status.cancelled'),
                'value' => $this->getCancelledPaymentStatus(),
            ],
        ];
    }

    public function getInvoiceStatusText(string $status): ?string
    {
        return match ($status) {
            $this->getInitPaymentStatus()      => __p('featured::phrase.invoice_status.init'),
            $this->getPendingPaymentStatus()   => __p('featured::phrase.invoice_status.pending_payment'),
            $this->getCompletedPaymentStatus() => __p('featured::phrase.invoice_status.completed'),
            $this->getCancelledPaymentStatus() => __p('featured::phrase.invoice_status.cancelled'),
            default                            => null,
        };
    }

    public function getSearchItemStatusOptions(): array
    {
        return [
            [
                'label' => __p('featured::phrase.item_status.unpaid'),
                'value' => Constants::FEATURED_ITEM_STATUS_UNPAID,
            ],
            [
                'label' => __p('featured::phrase.item_status.pending_payment'),
                'value' => Constants::FEATURED_ITEM_STATUS_PENDING_PAYMENT,
            ],
            [
                'label' => __p('featured::phrase.item_status.running'),
                'value' => Constants::FEATURED_ITEM_STATUS_RUNNING,
            ],
            [
                'label' => __p('featured::phrase.item_status.ended'),
                'value' => Constants::FEATURED_ITEM_STATUS_ENDED,
            ],
            [
                'label' => __p('featured::phrase.item_status.cancelled'),
                'value' => Constants::FEATURED_ITEM_STATUS_CANCELLED,
            ],
        ];
    }

    public function getItemStatusText(string $status): ?string
    {
        return match ($status) {
            Constants::FEATURED_ITEM_STATUS_CANCELLED       => __p('featured::phrase.item_status.cancelled'),
            Constants::FEATURED_ITEM_STATUS_ENDED           => __p('featured::phrase.item_status.ended'),
            Constants::FEATURED_ITEM_STATUS_UNPAID          => __p('featured::phrase.item_status.unpaid'),
            Constants::FEATURED_ITEM_STATUS_PENDING_PAYMENT => __p('featured::phrase.item_status.pending_payment'),
            Constants::FEATURED_ITEM_STATUS_RUNNING         => __p('featured::phrase.item_status.running'),
            default                                         => null,
        };
    }

    public function getTransactionStatusSearchOptions(): array
    {
        return [
            [
                'label' => __p('featured::phrase.invoice_status.pending_payment'),
                'value' => $this->getPendingPaymentStatus(),
            ],
            [
                'label' => __p('featured::phrase.invoice_status.completed'),
                'value' => $this->getCompletedPaymentStatus(),
            ],
        ];
    }

    public function getGatewaySearchOptions(): array
    {
        return resolve(GatewayRepositoryInterface::class)->getGatewaySearchOptions();
    }

    /**
     * @return array[]
     */
    public function getSearchFormResponsiveSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '50%',
                'md' => '220px',
            ],
            'width' => [
                'xs' => '100%',
                'sm' => '50%',
            ],
        ];
    }

    public function getPriceFormatted(float $price, string $currency): ?string
    {
        if ($price < 0) {
            return null;
        }

        return app('currency')->getPriceFormatByCurrencyId($currency, $price);
    }

    public function handleRequestEmptyStringValue(array $data, string $index): array
    {
        if (!Arr::has($data, $index)) {
            return $data;
        }

        $value = Arr::get($data, $index);

        if (!is_string($value)) {
            Arr::forget($data, $index);

            return $data;
        }

        $value = trim($value);

        if (MetaFoxConstant::EMPTY_STRING === $value) {
            Arr::forget($data, $index);

            return $data;
        }

        return $data;
    }

    public function handleRequestEmptyIntegerValue(array $data, string $index): array
    {
        if (!Arr::has($data, $index)) {
            return $data;
        }

        $value = Arr::get($data, $index);

        if (!is_numeric($value)) {
            Arr::forget($data, $index);

            return $data;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getPackageSearchOptions(): array
    {
        return $this->packageRepository->getPackageSearchOptions();
    }

    public function getTransactionIdForFree(): string
    {
        return uniqid('f_', true);
    }

    public function getInvoiceStatusColors(): array
    {
        return [
            $this->getCompletedPaymentStatus() => [
                'label' => __p('featured::phrase.invoice_status.completed'),
                'color' => Constants::PAID_COLOR,
            ],
            $this->getCancelledPaymentStatus() => [
                'label' => __p('featured::phrase.invoice_status.cancelled'),
                'color' => Constants::CANCELLED_COLOR,
            ],
            $this->getInitPaymentStatus() => [
                'label' => __p('featured::phrase.invoice_status.init'),
                'color' => Constants::UNPAID_COLOR,
            ],
            $this->getPendingPaymentStatus() => [
                'label' => __p('featured::phrase.invoice_status.pending_payment'),
                'color' => Constants::PENDING_PAYMENT_COLOR,
            ],
        ];
    }

    public function getInvoiceStatusInfo(string $status): ?array
    {
        $infos = $this->getInvoiceStatusColors();

        return Arr::get($infos, $status);
    }

    public function getEntityTypeLabelByEntityType(string $entityType): ?string
    {
        $itemTypes = $this->getAllowedEntityTypes();

        if (!count($itemTypes)) {
            return null;
        }

        $itemType = Arr::get($itemTypes, $entityType);

        if (!is_array($itemType)) {
            return null;
        }

        $moduleId = Arr::get($itemType, 'module_id');

        if (!is_string($moduleId)) {
            return null;
        }

        return $this->getEntityTypeLabel($moduleId, $entityType);
    }

    /**
     * @param  string      $option
     * @return string|null
     */
    public function getPricingLabel(string $option): ?string
    {
        $options = LoadReduce::remember('feature::support::getPricingLabelOptions', function () {
            return collect($this->getPricingOptions())
                ->keyBy('value')
                ->toArray();
        });

        return Arr::get($options, sprintf('%s.label', $option));
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

    public function getItemTitle(?Content $content): string
    {
        if (null === $content) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        $title = $content->toTitle();

        if (MetaFoxConstant::EMPTY_STRING !== $title) {
            return $title;
        }

        $featuredData = $content->toFeaturedData();

        if (!is_array($featuredData)) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        $title = Arr::get($featuredData, 'title');

        if (!is_string($title)) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return trim($title);
    }

    public function getAllowedRoleOptions(): array
    {
        $options = resolve(RoleRepositoryInterface::class)->getRoleOptions();

        $disallowedRoleIds = [UserRole::SUPER_ADMIN_USER, UserRole::GUEST_USER, UserRole::BANNED_USER];

        return array_values(array_filter($options, function ($option) use ($disallowedRoleIds) {
            return !in_array($option['value'], $disallowedRoleIds);
        }));
    }

    public function getAllowedRole(): array
    {
        return Arr::pluck($this->getAllowedRoleOptions(), 'value');
    }
}
