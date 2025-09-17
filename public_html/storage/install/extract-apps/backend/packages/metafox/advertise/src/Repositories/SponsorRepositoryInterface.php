<?php

namespace MetaFox\Advertise\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Advertise\Models\Invoice;
use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Platform\Contracts\Content;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User;

/**
 * Interface Sponsor.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface SponsorRepositoryInterface
{
    /**
     * @param  string       $itemType
     * @param  int          $itemId
     * @return Content|null
     */
    public function getMorphedItem(string $itemType, int $itemId): ?Content;

    /**
     * @param  string          $itemType
     * @param  array           $itemIds
     * @return Collection|null
     */
    public function getMorphedItems(string $itemType, array $itemIds): ?Collection;

    /**
     * @param  string $itemType
     * @return mixed
     */
    public function getItemPolicy(string $itemType): mixed;

    /**
     * @param  User    $context
     * @param  Content $item
     * @return Sponsor
     */
    public function sponsor(User $context, Content $item): Sponsor;

    /**
     * @param  User    $context
     * @param  Content $item
     * @return bool
     */
    public function unsponsor(User $context, Content $item): bool;

    /**
     * @param  User    $context
     * @param  Content $item
     * @return bool
     */
    public function unsponsorFeed(User $context, Content $item): bool;

    /**
     * @param  User    $context
     * @param  Content $item
     * @param  array   $attributes
     * @return Sponsor
     */
    public function createFeedSponsor(User $context, Content $item, array $attributes): Sponsor;

    /**
     * @param  User    $context
     * @param  Content $item
     * @param  array   $attributes
     * @return Sponsor
     */
    public function createSponsor(User $context, Content $item, array $attributes): Sponsor;

    /**
     * @param  Sponsor $sponsor
     * @param  Invoice $invoice
     * @return bool
     */
    public function updateSuccessPayment(Sponsor $sponsor, Invoice $invoice): bool;

    /**
     * @param  string $entityType
     * @param  bool   $clearPending
     * @return void
     */
    public function clearCachesByEntityType(string $entityType, bool $clearPending = false): void;

    /**
     * @param  Content $item
     * @return void
     */
    public function deleteDataByItem(Content $item): void;

    /**
     * @param  Sponsor $sponsor
     * @return void
     */
    public function deleteData(Sponsor $sponsor): void;

    /**
     * @param  Content $content
     * @return bool
     */
    public function isApprovedSponsor(Content $content): bool;

    /**
     * @param  Content $content
     * @return bool
     */
    public function isPendingSponsor(Content $content): bool;

    /**
     * @param  Content $content
     * @return bool
     */
    public function updateTotal(Content $content): bool;

    /**
     * @param  User       $user
     * @param  string     $itemType
     * @param  int|null   $limit
     * @param  array|null $loadedItemIds
     * @param  bool       $shuffle
     * @return array
     */
    public function getSponsoredItemIdsByType(User $user, string $itemType, ?int $limit = null, ?array $loadedItemIds = null, bool $shuffle = false): array;

    /**
     * @param  User    $user
     * @param  Sponsor $sponsor
     * @return bool
     */
    public function approveSponsor(User $user, Sponsor $sponsor): bool;

    /**
     * @param  User    $user
     * @param  Sponsor $sponsor
     * @return bool
     */
    public function denySponsor(User $user, Sponsor $sponsor): bool;

    /**
     * @param  User    $user
     * @param  Sponsor $sponsor
     * @param  array   $attributes
     * @return Sponsor
     */
    public function updateSponsor(User $user, Sponsor $sponsor, array $attributes): Sponsor;

    /**
     * @param  User    $context
     * @param  Content $item
     * @return Sponsor
     */
    public function sponsorFeed(User $context, Content $item): Sponsor;

    /**
     * @param  Sponsor $sponsor
     * @return bool
     */
    public function deleteSponsor(Sponsor $sponsor): bool;

    /**
     * @param  User      $user
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewSponsors(User $user, array $attributes = []): Paginator;

    /**
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewAdminCP(array $attributes = []): Paginator;

    /**
     * @param  Sponsor $sponsor
     * @param  bool    $isActive
     * @return bool
     */
    public function activeSponsor(Sponsor $sponsor, bool $isActive): bool;

    /**
     * @param  User    $context
     * @param  Sponsor $sponsor
     * @return bool
     */
    public function markAsPaid(User $context, Sponsor $sponsor): bool;
}
