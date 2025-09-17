<?php

namespace MetaFox\Marketplace\Tests\Unit\Repositories\Eloquent\Listing;

use MetaFox\Marketplace\Models\Category;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Repositories\Eloquent\ListingRepository;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ListingRepositoryUpdateMarketplaceListingTest extends TestCase
{
    /**
     * @return Model
     */
    public function testInstance(): Model
    {
        $repository = resolve(ListingRepositoryInterface::class);
        $this->assertInstanceOf(ListingRepository::class, $repository);
        $this->assertTrue(true);
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $this->assertNotEmpty($item);

        return $item;
    }

    public function testUpdateListing()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();

        /** @var ListingRepository $repository */
        $repository = resolve(ListingRepositoryInterface::class);
        $title      = $this->faker->title;
        $listing    = Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        $params = [
            'title'      => $title,
            'categories' => [$category->id],
            'text'       => $this->faker->text,
        ];

        $listing = $repository->updateMarketplaceListing($user, $listing->entityId(), $params);

        $this->assertTrue(($listing->title == $title));

        $categoryResult = $listing->categories->first();
        $this->assertNotEmpty($categoryResult);
        $this->assertTrue(($categoryResult->id == $category->id));
    }
}
