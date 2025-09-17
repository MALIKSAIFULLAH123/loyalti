<?php

namespace MetaFox\Marketplace\Tests\Unit\Repositories\Eloquent;

use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Repositories\Eloquent\ListingRepository;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\ViewScope;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ListingRepositoryViewListingsTest extends TestCase
{
    protected ListingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(ListingRepositoryInterface::class);
    }

    public function testInstance()
    {
        $repository = resolve(ListingRepositoryInterface::class);
        $this->assertInstanceOf(ListingRepository::class, $repository);

        return [
            'q'           => '',
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'view'        => ViewScope::VIEW_DEFAULT,
            'limit'       => Pagination::DEFAULT_ITEM_PER_PAGE,
            'category_id' => 0,
            'user_id'     => 0,
        ];
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewMyListingsWithSponsoredListings(array $params)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $owner = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setOwner($user)->setUser($user)->create([
            'privacy'    => MetaFoxPrivacy::EVERYONE,
            'is_sponsor' => Model::IS_SPONSOR,
        ]);

        Model::factory()->setOwner($user)->setUser($user)->create([
            'privacy'    => MetaFoxPrivacy::EVERYONE,
            'is_sponsor' => 0,
        ]);

        $mySponsoredItem = Model::factory()->setOwner($owner)->setUser($owner)->create([
            'privacy'    => MetaFoxPrivacy::EVERYONE,
            'is_sponsor' => Model::IS_SPONSOR,
        ]);

        $myNormalItem = Model::factory()->setOwner($owner)->setUser($owner)->create([
            'privacy'    => MetaFoxPrivacy::EVERYONE,
            'is_sponsor' => 0,
        ]);

        $params = array_merge($params, [
            'view' => Browse::VIEW_MY,
        ]);

        $results = $this->repository->viewMarketplaceListings($owner, $owner, $params)->collect();

        $this->expectNotToPerformAssertions();
    }
}
