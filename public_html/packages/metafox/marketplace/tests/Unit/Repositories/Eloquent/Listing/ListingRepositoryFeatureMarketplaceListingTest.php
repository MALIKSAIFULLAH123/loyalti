<?php

namespace MetaFox\Marketplace\Tests\Unit\Repositories\Eloquent\Listing;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Repositories\Eloquent\ListingRepository;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ListingRepositoryFeatureMarketplaceListingTest extends TestCase
{
    /**
     * @return Model
     */
    public function testInstance(): Model
    {
        $repository = resolve(ListingRepositoryInterface::class);
        $this->assertInstanceOf(ListingRepository::class, $repository);
        $this->assertTrue(true);

        $item = Model::factory()->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $this->assertNotEmpty($item);

        return $item;
    }

    /**
     * @depends testInstance
     *
     * @param Model $item
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function testFeatureListing(Model $item): Model
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var ListingRepository $repository */
        $repository = resolve(ListingRepositoryInterface::class);

        $repository->feature($admin, $item->id, 1);
        $item->refresh();
        $this->assertTrue(!empty($item->is_featured));

        return $item;
    }

    /**
     * @depends testFeatureListing
     *
     * @param Model $item
     *
     * @throws AuthorizationException
     */
    public function testRemoveFeatureListing(Model $item)
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var ListingRepository $repository */
        $repository = resolve(ListingRepositoryInterface::class);

        $repository->feature($admin, $item->id, 0);
        $item->refresh();
        $this->assertTrue(empty($item->is_featured));
    }
}
