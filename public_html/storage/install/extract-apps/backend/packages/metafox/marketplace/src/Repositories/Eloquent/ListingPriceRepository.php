<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Marketplace\Repositories\ListingPriceRepositoryInterface;
use MetaFox\Marketplace\Models\ListingPrice;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ListingPriceRepository.
 */
class ListingPriceRepository extends AbstractRepository implements ListingPriceRepositoryInterface
{
    public function model()
    {
        return ListingPrice::class;
    }
}
