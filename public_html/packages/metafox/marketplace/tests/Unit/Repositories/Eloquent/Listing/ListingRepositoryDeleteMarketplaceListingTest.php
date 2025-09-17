<?php

namespace MetaFox\Marketplace\Tests\Unit\Repositories\Eloquent\Listing;

use Exception;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Models\Text;
use MetaFox\Marketplace\Repositories\Eloquent\ListingRepository;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ListingRepositoryDeleteMarketplaceListingTest extends TestCase
{
    /**
     * @return Model
     */
    public function testInstance(): Model
    {
        $repository = resolve(ListingRepositoryInterface::class);
        $this->assertInstanceOf(ListingRepository::class, $repository);

        $item = Model::factory()->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $this->assertNotEmpty($item);

        return $item;
    }

    /**
     * @depends testInstance
     *
     * @param Model $item
     *
     * @throws Exception
     */
    public function testDeleteListing(Model $item)
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var ListingRepository $repository */
        $repository = resolve(ListingRepositoryInterface::class);

        $repository->deleteMarketplaceListing($admin, $item->id);
        $this->assertEmpty(Model::query()->find($item->id));
        $this->assertEmpty(Text::query()->find($item->id));
    }
}
