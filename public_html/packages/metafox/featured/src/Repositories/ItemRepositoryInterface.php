<?php

namespace MetaFox\Featured\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Models\Package;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\Content;

/**
 * Interface Item.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ItemRepositoryInterface
{
    /**
     * @param  User    $user
     * @param  Content $content
     * @return bool
     */
    public function isFeaturedByUser(User $user, Content $content): bool;

    /**
     * @param  User    $user
     * @param  Content $content
     * @return Item
     */
    public function createItemForFree(User $user, Content $content): Item;

    /**
     * @param  User    $user
     * @param  Content $content
     * @param  array   $attributes
     * @return Item
     */
    public function createItem(User $user, Content $content, array $attributes): Item;

    /**
     * @param  int     $packageId
     * @return Package
     */
    public function getPackage(int $packageId): Package;

    /**
     * @param  Content $content
     * @param  Package $package
     * @return void
     */
    public function validatePackage(Content $content, Package $package): void;

    /**
     * @param  Item $item
     * @return bool
     */
    public function markItemRunning(Item $item): bool;

    /**
     * @param  Item $item
     * @return bool
     */
    public function markItemPendingPayment(Item $item): bool;

    /**
     * @param  Item $item
     * @return bool
     */
    public function deleteItem(Item $item): bool;

    /**
     * @param  User $user
     * @param  Item $item
     * @return bool
     */
    public function markItemCancelled(User $user, Item $item): bool;

    /**
     * @param  Item $item
     * @return bool
     */
    public function markItemEnded(Item $item): bool;

    /**
     * @param  User $user
     * @return void
     */
    public function deleteUserData(User $user): void;

    /**
     * @param  Item $item
     * @return bool
     */
    public function handleCancellingRunningItemBySystem(Item $item): bool;

    /**
     * @param  User    $user
     * @param  Content $content
     * @return bool
     */
    public function markItemCancelledByUnFeaturingContent(User $user, Content $content): bool;

    /**
     * @param  Content $content
     * @return bool
     */
    public function handleContentDeleted(Content $content): bool;

    /**
     * @param  Item $item
     * @return bool
     */
    public function markItemCancelledForDeletedContent(Item $item): bool;

    /**
     * @param  Content $content
     * @return bool
     */
    public function isContentAvailableForFeature(Content $content): bool;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewItems(User $context, array $attributes = []): Paginator;

    /**
     * @param  InvoiceRepositoryInterface $invoiceRepository
     * @return self
     */
    public function setInvoiceRepository(InvoiceRepositoryInterface $invoiceRepository): self;

    /**
     * @param  PackageRepositoryInterface $packageRepository
     * @return self
     */
    public function setPackageRepository(PackageRepositoryInterface $packageRepository): self;

    /**
     * @param Item $item
     * @return bool
     */
    public function markItemFree(Item $item): bool;
}
