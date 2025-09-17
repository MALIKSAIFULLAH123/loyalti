<?php

namespace MetaFox\Advertise\Support\Contracts;

use MetaFox\Advertise\Contracts\AdvertisePaymentInterface;
use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

interface SupportInterface
{
    /**
     * @return array
     */
    public function getSponsorTypeOptions(): array;

    /**
     * @return array
     */
    public function getAllowSponsorTypes(): array;

    /**
     * @return array
     */
    public function getPlacementTypes(): array;

    /**
     * @return string
     */
    public function getPendingActionStatus(): string;

    /**
     * @return array
     */
    public function getDisallowedUserRoleOptions(): array;

    /**
     * @return array
     */
    public function getUserRoleOptions(): array;

    /**
     * @return array
     */
    public function getDeleteOptions(): array;

    /**
     * @return array
     */
    public function getAdvertiseTypes(): array;

    /**
     * @param  User        $context
     * @param  bool        $isFree
     * @param  string|null $currencyId
     * @param  bool|null   $isActive
     * @return array
     */
    public function getPlacementOptions(User $context, bool $isFree = false, ?string $currencyId = null, ?bool $isActive = true): array;

    /**
     * @return array
     */
    public function getGenderOptions(): array;

    /**
     * @return array
     */
    public function getLanguageOptions(): array;

    /**
     * @return string
     */
    public function getCancelledPaymentStatus(): string;

    /**
     * @return string
     */
    public function getCompletedPaymentStatus(): string;

    /**
     * @return string
     */
    public function getPendingPaymentStatus(): string;

    /**
     * @return array
     */
    public function getAdvertiseStatusOptions(): array;

    /**
     * @return array
     */
    public function getActiveOptions(): array;

    /**
     * @return array
     */
    public function getAllowedViews(): array;

    /**
     * @param  Sponsor $sponsor
     * @return bool
     */
    public function isSponsorChangePrice(Sponsor $sponsor): bool;

    /**
     * @param  Advertise $advertise
     * @return bool
     */
    public function isAdvertiseChangePrice(Advertise $advertise): bool;

    /**
     * @param  int        $placementId
     * @param  string     $currencyId
     * @return float|null
     */
    public function getPlacementPriceByCurrencyId(int $placementId, string $currencyId): ?float;

    /**
     * @param  User      $user
     * @param  bool|null $isActive
     * @return array
     */
    public function getAvailablePlacements(User $user, ?bool $isActive = true): array;

    /**
     * @param  Sponsor $sponsor
     * @param  float   $price
     * @return float
     */
    public function calculateSponsorPrice(Sponsor $sponsor, float $price): float;

    /**
     * @param  Advertise  $advertise
     * @param  float|null $placementPrice
     * @return float|null
     */
    public function calculateAdvertisePrice(Advertise $advertise, ?float $placementPrice): ?float;

    /**
     * @return array
     */
    public function getInvoiceStatuses(): array;

    /**
     * @return array
     */
    public function getAdvertiseStatuses(): array;

    /**
     * @return array
     */
    public function getInvoiceStatusOptions(): array;

    /**
     * @return array
     */
    public function getAllowedLocations(): array;

    /**
     * @param  Advertise $advertise
     * @return int|null
     */
    public function getAmount(Advertise $advertise): ?int;

    /**
     * @param  Advertise $advertise
     * @return int|null
     */
    public function getCurrentAmount(Advertise $advertise): ?int;

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
     * @return array
     */
    public function getActivePlacementsForSetting(): array;

    /**
     * @param  Sponsor    $sponsor
     * @return float|null
     */
    public function getCurrentSponsorPrice(Sponsor $sponsor): ?float;

    /**
     * @param  Content $content
     * @return bool
     */
    public function isPendingSponsor(Content $content): bool;

    /**
     * @param  Content $content
     * @return bool
     */
    public function isApprovedSponsor(Content $content): bool;

    /**
     * @param  string     $status
     * @return array|null
     */
    public function getAdvertiseStatusInfo(string $status): ?array;

    /**
     * @return array
     */
    public function getAdvertiseStatusColors(): array;

    /**
     * @param  array $prices
     * @return array
     */
    public function roundPriceUp(array $prices): array;

    /**
     * @return bool
     */
    public function isUsingMultiStepFormForEwallet(): bool;

    /**
     * @param  int                            $itemId
     * @param  string                         $itemType
     * @return AdvertisePaymentInterface|null
     */
    public function getMorphedModel(int $itemId, string $itemType): ?AdvertisePaymentInterface;

    /**
     * @param Sponsor $sponsor
     * @return bool
     */
    public function isFreeSponsorInvoice(Sponsor $sponsor): bool;
}
