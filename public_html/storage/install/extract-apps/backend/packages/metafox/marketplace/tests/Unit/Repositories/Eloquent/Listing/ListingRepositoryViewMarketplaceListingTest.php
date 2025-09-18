<?php

namespace MetaFox\Marketplace\Tests\Unit\Repositories\Eloquent\Listing;

use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Repositories\Eloquent\ListingRepository;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ListingRepositoryViewMarketplaceListingTest extends TestCase
{
    public function testInstance(): Model
    {
        $repository = resolve(ListingRepositoryInterface::class);
        $this->assertInstanceOf(ListingRepository::class, $repository);
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0]);
        $this->assertNotEmpty($item);

        return $item;
    }

    /**
     * @depends testInstance
     */
    public function testViewSingleListing(Model $item)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $repository = resolve(ListingRepositoryInterface::class);
        $result     = $repository->viewMarketplaceListing($user, $item->id);

        $this->assertTrue(($item->id == $result->id));
    }
}
