<?php

namespace MetaFox\Marketplace\Tests\Unit\Repositories\Eloquent\Listing;

use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Repositories\Eloquent\ListingRepository;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\MetaFoxPrivacy;
use Tests\TestCase;

class ListingRepositoryReopenMarketplaceListingTest extends TestCase
{
    /**
     * @return Model
     */
    public function testInstance(): Model
    {
        $repository = resolve(ListingRepositoryInterface::class);
        $this->assertInstanceOf(ListingRepository::class, $repository);

        $item = Model::factory()->create([
            'privacy' => MetaFoxPrivacy::EVERYONE,
            'is_sold' => 1,
        ]);
        $this->assertNotEmpty($item);

        return $item;
    }
}
