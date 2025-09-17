<?php

namespace MetaFox\Marketplace\Tests\Unit\Support\Browse\Scope\Listing;

use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\Eloquent\ListingRepository;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\ViewScope;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewScopeTest extends TestCase
{
    protected ListingRepositoryInterface $repository;

    public function setup(): void
    {
        parent::setUp();
        $this->repository = resolve(ListingRepositoryInterface::class);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(ListingRepository::class, $this->repository);
    }

    /**
     * @depends testInstance
     */
    public function testViewDefault()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->be($user);

        $items = Listing::factory()->count(2)->setUser($user)->setOwner($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
            'is_sponsor'  => 0,
            'is_approved' => 1,
        ]);

        $this->assertNotEmpty($items->toArray());

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_ALL)->setUserContext($user)->setIsViewOwner(true);

        $result = $this->repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(2);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testInstance
     */
    public function testWithViewMy()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $items = Listing::factory()->count(2)->setUser($user)->setOwner($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
            'is_sponsor'  => 0,
            'is_approved' => 1,
        ]);

        $this->assertNotEmpty($items->toArray());

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_MY)->setUserContext($user)->setIsViewOwner(true);

        $result = $this->repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(2);

        $this->assertNotEmpty($result->items());
    }
}
