<?php

namespace MetaFox\Featured\Contracts;

use Carbon\CarbonInterface;
use MetaFox\Featured\Models\Item;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

interface SupportInterface
{
    /**
     * @return array
     */
    public function getDurationOptions(): array;

    /**
     * @param  string|null $userCurrency
     * @return array
     */
    public function getCurrencyOptions(?string $userCurrency = null): array;

    /**
     * @return array
     */
    public function getApplicableItemTypeOptions(): array;

    /**
     * @return array
     */
    public function getApplicableRoleOptions(): array;

    /**
     * @return array
     */
    public function getDurationOptionsForSearch(): array;

    /**
     * @return array
     */
    public function getPricingOptions(): array;

    /**
     * @return array
     */
    public function getStatusOptions(): array;

    /**
     * @param  string|null $durationPeriod
     * @param  int|null    $durationValue
     * @return string
     */
    public function getDurationText(?string $durationPeriod, ?int $durationValue): string;

    /**
     * @param  string $moduleId
     * @param  string $entityType
     * @return string
     */
    public function getEntityTypeLabel(string $moduleId, string $entityType): string;

    /**
     * @param  string $entityType
     * @return bool
     */
    public function isAllowedEntityType(string $entityType): bool;

    /**
     * @return array
     */
    public function getAllowedEntityTypes(): array;

    /**
     * @param  string  $entityType
     * @param  int     $entityId
     * @return Content
     */
    public function morphItemFromEntityType(string $entityType, int $entityId): Content;

    /**
     * @return string
     */
    public function getCompletedPaymentStatus(): string;

    /**
     * @return string
     */
    public function getPendingPaymentStatus(): string;

    /**
     * @return string
     */
    public function getAllPaymentStatus(): string;

    /**
     * @return string
     */
    public function getCancelledPaymentStatus(): string;

    /**
     * @return string
     */
    public function getInitPaymentStatus(): string;

    /**
     * @param  Content|null $item
     * @return void
     */
    public function activateItemFeatured(?Content $item): void;

    /**
     * @param  Content|null $item
     * @return void
     */
    public function deactivateItemFeatured(?Content $item): void;

    /**
     * @param  int|null $packageId
     * @return void
     */
    public function increasePackageTotalActive(?int $packageId): void;

    /**
     * @param  int|null $packageId
     * @return void
     */
    public function increasePackageTotalEnd(?int $packageId): void;

    /**
     * @param  int|null $packageId
     * @return void
     */
    public function increasePackageTotalCancelled(?int $packageId): void;

    /**
     * @param  int|null $packageId
     * @return void
     */
    public function decreasePackageTotalActive(?int $packageId): void;

    /**
     * @param  int|null $packageId
     * @return void
     */
    public function decreasePackageTotalEnd(?int $packageId): void;

    /**
     * @param  int|null $packageId
     * @return void
     */
    public function decreasePackageTotalCancelled(?int $packageId): void;

    /**
     * @param  User   $user
     * @param  string $entityType
     * @return bool
     */
    public function hasAvailablePackages(User $user, string $entityType): bool;

    /**
     * @param  string|null          $durationPeriod
     * @param  int|null             $durationValue
     * @return CarbonInterface|null
     */
    public function getExpiredDatetimeByDuration(?string $durationPeriod, ?int $durationValue): ?CarbonInterface;

    /**
     * @param  Item $item
     * @return void
     */
    public function markFeaturedItemEnded(Item $item): void;

    /**
     * @param  Content $content
     * @return void
     */
    public function handleContentDeleted(Content $content): void;

    /**
     * @param  Item $item
     * @return void
     */
    public function handleItemDeleted(Item $item): void;

    /**
     * @param  Content $content
     * @return bool
     */
    public function isContentAvailableForFeature(Content $content): bool;

    /**
     * @param  User    $user
     * @param  Content $content
     * @return bool
     */
    public function isFeaturedByUser(User $user, Content $content): bool;

    /**
     * @return array
     */
    public function getSearchInvoiceStatusOptions(): array;

    /**
     * @param  string      $status
     * @return string|null
     */
    public function getInvoiceStatusText(string $status): ?string;

    /**
     * @return array
     */
    public function getSearchItemStatusOptions(): array;

    /**
     * @param  string      $status
     * @return string|null
     */
    public function getItemStatusText(string $status): ?string;

    /**
     * @return array
     */
    public function getTransactionStatusSearchOptions(): array;

    /**
     * @return array
     */
    public function getGatewaySearchOptions(): array;

    /**
     * @return array
     */
    public function getSearchFormResponsiveSx(): array;

    /**
     * @param  float       $price
     * @param  string      $currency
     * @return string|null
     */
    public function getPriceFormatted(float $price, string $currency): ?string;

    /**
     * @param  array  $data
     * @param  string $index
     * @return array
     */
    public function handleRequestEmptyStringValue(array $data, string $index): array;

    /**
     * @param  array  $data
     * @param  string $index
     * @return array
     */
    public function handleRequestEmptyIntegerValue(array $data, string $index): array;

    /**
     * @return array
     */
    public function getPackageSearchOptions(): array;

    /**
     * @return string
     */
    public function getTransactionIdForFree(): string;

    /**
     * @return array
     */
    public function getInvoiceStatusColors(): array;

    /**
     * @param  string     $status
     * @return array|null
     */
    public function getInvoiceStatusInfo(string $status): ?array;

    /**
     * @param  string      $entityType
     * @return string|null
     */
    public function getEntityTypeLabelByEntityType(string $entityType): ?string;

    /**
     * @param  string      $option
     * @return string|null
     */
    public function getPricingLabel(string $option): ?string;

    /**
     * @return bool
     */
    public function isUsingMultiStepFormForEwallet(): bool;

    /**
     * @param  Content|null $content
     * @return string|null
     */
    public function getItemTitle(?Content $content): ?string;

    /**
     * @return array
     */
    public function getAllowedRoleOptions(): array;

    /**
     * @return array
     */
    public function getAllowedRole(): array;
}
