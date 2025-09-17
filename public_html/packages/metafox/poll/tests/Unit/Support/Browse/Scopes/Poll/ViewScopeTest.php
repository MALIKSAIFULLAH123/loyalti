<?php

namespace MetaFox\Poll\Tests\Unit\Support\Browse\Scopes\Poll;

use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\Poll\Support\Browse\Scopes\Poll\ViewScope;
use Tests\TestCase;

class ViewScopeTest extends TestCase
{
    /**
     * @return PollRepository
     */
    public function testInstance(): PollRepository
    {
        $repository = resolve(PollRepositoryInterface::class);
        $this->assertInstanceOf(PollRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     */
    public function testViewDefault(PollRepository $repository)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->be($user);

        $items = Poll::factory()->count(2)->setUser($user)->setOwner($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
            'is_sponsor'  => 0,
        ]);

        $this->assertNotEmpty($items->toArray());

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_ALL)->setUserContext($user)->setIsViewOwner(true);

        $result = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(2);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testInstance
     */
    public function testWithViewMy(PollRepository $repository)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $items = Poll::factory()->count(2)->setUser($user)->setOwner($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
            'is_sponsor'  => 0,
        ]);

        $this->assertNotEmpty($items->toArray());

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_MY)->setUserContext($user)->setIsViewOwner(true);

        $result = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(2);

        $this->assertNotEmpty($result->items());
    }
}
