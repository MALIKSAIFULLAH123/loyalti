<?php

namespace MetaFox\Marketplace\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use Prettus\Validator\Exceptions\ValidatorException;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Interface ListingRepositoryInterface.
 * @method Listing find($id, $columns = ['*'])
 * @method Listing getModel()
 *
 * @mixin UserMorphTrait
 */
interface ListingRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewMarketplaceListings(User $context, User $owner, array $attributes): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Listing
     * @throws AuthorizationException
     */
    public function viewMarketplaceListing(User $context, int $id): Listing;

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Listing
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createMarketplaceListing(User $context, User $owner, array $attributes): Listing;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Listing
     */
    public function updateMarketplaceListing(User $context, int $id, array $attributes): Listing;

    /**
     * Delete a marketplace listing.
     *
     * @param User $context
     * @param int  $id
     *
     * @return bool
     */
    public function deleteMarketplaceListing(User $context, int $id): bool;

    /**
     * @param int $limit
     *
     * @return Paginator
     */
    public function findFeature(int $limit = 4): Paginator;

    /**
     * @param int $limit
     *
     * @return Paginator
     */
    public function findSponsor(int $limit = 4): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param  int  $id
     * @return void
     */
    public function forceDeleteListing(int $id): void;

    /**
     * @param  int  $id
     * @return bool
     */
    public function closeListingAfterPayment(int $id): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function reopenListing(User $context, int $id): bool;

    /**
     * @return void
     */
    public function sendExpiredNotifications(): void;

    /**
     * @return void
     */
    public function migratePrices(): void;

    /**
     * @param  Listing $listing
     * @return void
     */
    public function addPrices(Listing $listing): void;

    /**
     * @param  User  $context
     * @param  array $attributes
     * @return array
     */
    public function getListingForForm(User $context, array $attributes = []): array;
}
