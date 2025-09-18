<?php

namespace MetaFox\Marketplace\Repositories;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Enumerable;
use MetaFox\Marketplace\Models\Invite;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Platform\Contracts\User;

/**
 * Interface ListingRepositoryInterface.
 * @method Invite find($id, $columns = ['*'])
 * @method Invite getModel()
 */
interface InviteRepositoryInterface
{
    /**
     * @param  int   $userId
     * @param  int   $listingId
     * @param  array $userIds
     * @return void
     */
    public function inviteFriendsToListing(int $userId, int $listingId, array $userIds): void;

    /**
     * @param  User  $context
     * @param  array $attributes
     * @return bool
     */
    public function inviteToListing(User $context, array $attributes): bool;

    /**
     * @param  int   $listingId
     * @return array
     */
    public function getInvitedUserIds(int $listingId): array;

    /**
     * @param  User    $context
     * @param  Listing $listing
     * @return void
     */
    public function visitedAt(User $context, Listing $listing): void;

    /**
     * @param  User        $context
     * @param  int         $listingId
     * @return Invite|null
     */
    public function getInvite(User $context, int $listingId): ?Invite;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewInvitedPeople(User $context, array $attributes = []): Paginator;
}
