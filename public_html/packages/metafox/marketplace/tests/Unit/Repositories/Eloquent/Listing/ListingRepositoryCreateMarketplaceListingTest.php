<?php

namespace MetaFox\Marketplace\Tests\Unit\Repositories\Eloquent\Listing;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Marketplace\Models\Category;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\Eloquent\ListingRepository;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

/**
 * Class ListingRepositoryCreateMarketplaceListingTest.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListingRepositoryCreateMarketplaceListingTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(ListingRepositoryInterface::class);
        $this->assertInstanceOf(ListingRepository::class, $repository);
        $this->assertTrue(true);
    }

    /**
     * @depends testInstance
     * @throws Exception
     */
    public function testCreateListing()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();

        /** @var ListingRepository $repository */
        $repository = resolve(ListingRepositoryInterface::class);
        $params     = Listing::factory()->makeOne()->toArray();

        $params['categories'] = [$category->id];

        $item = $repository->createMarketplaceListing($user, $user, $params);

        $this->assertNotEmpty($item->id);

        $categoryResult = $item->categories->first();
        $this->assertNotEmpty($categoryResult);
        $this->assertTrue(($categoryResult->id == $category->id));
    }

    /**
     * @throws Exception
     */
    public function testCreateListingWithOwnerUser()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();
        /** @var ListingRepository $repository */
        $repository = resolve(ListingRepositoryInterface::class);
        $params     = [
            'title'             => $this->faker->title,
            'categories'        => [$category->entityId()],
            'short_description' => $this->faker->realTextBetween(10, 100),
            'text'              => $this->faker->text,
            'price'             => $this->faker->numberBetween(0, 5000),
            'currency_id'       => 'USD',
            'country_state'     => ['HCM'],
            'city'              => $this->faker->city,
            'postal_code'       => $this->faker->postcode,
            'privacy'           => MetaFoxPrivacy::EVERYONE,
            'allow_payment'     => 1,
            'is_approved'       => 0,
        ];
        $this->expectException(AuthorizationException::class);
        $repository->createMarketplaceListing($user, $user2, $params);
    }
}
