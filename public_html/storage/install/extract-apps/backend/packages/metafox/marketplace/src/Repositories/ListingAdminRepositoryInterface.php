<?php

namespace MetaFox\Marketplace\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Interface ListingAdminRepositoryInterface.
 * @method Listing find($id, $columns = ['*'])
 * @method Listing getModel()
 *
 * @mixin UserMorphTrait
 */
interface ListingAdminRepositoryInterface extends HasSponsor, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    public function viewMarketplaceListings(User $context, array $attributes): Builder;

    /**
     * @param User $context
     * @param int  $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     */
    public function deleteMarketplaceListing(User $context, int $id): bool;
}
