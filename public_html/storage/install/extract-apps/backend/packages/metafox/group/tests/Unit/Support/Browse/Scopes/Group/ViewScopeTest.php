<?php

namespace MetaFox\Group\Tests\Unit\Support\Browse\Scopes\Group;

use MetaFox\Friend\Models\Friend;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\Group\ViewScope;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewScopeTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(GroupRepositoryInterface::class);
        $this->assertInstanceOf(GroupRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     */
    public function testViewDefault(GroupRepositoryInterface $repository)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Group::factory()->count(2)
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_ALL)->setUserContext($user);

        $result = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(4);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testInstance
     */
    public function testViewMy(GroupRepositoryInterface $repository)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Group::factory()->count(2)
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_MY)->setUserContext($user);

        $result = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(10);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testInstance
     */
    public function testViewFiend(GroupRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Friend::factory()->setUser($user)->setOwner($user2)->create();
        Friend::factory()->setUser($user2)->setOwner($user)->create();

        $checkCount = 2;
        Group::factory()->count($checkCount)
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create(['is_approved' => 0]);

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_FRIEND)->setUserContext($user2);

        $result = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(20);

        $this->assertTrue($checkCount == count($result->items()));
    }

    /**
     * @depends testInstance
     */
    public function testViewPending(GroupRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::ADMIN_USER);

        Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create(['is_approved' => 0]);

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_PENDING)->setUserContext($user2);

        $result = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(5);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testInstance
     */
    public function testViewJoined(GroupRepositoryInterface $repository)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $viewScope = new ViewScope();
        $viewScope->setView(ViewScope::VIEW_JOINED)->setUserContext($user);

        $result = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(5);

        $checkCount = 1;
        $this->assertTrue($checkCount == count($result->items()));
    }

    /**
     * @depends testInstance
     */
    public function testViewOnProfile(GroupRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $viewScope = new ViewScope();
        $viewScope->setView(ViewScope::VIEW_JOINED)->setUserContext($user2)->setIsViewProfile(true);

        $result = $repository->getModel()->newQuery()
            ->addScope($viewScope)
            ->where('groups.user_id', '=', $user->entityId())
            ->where('groups.is_approved', Group::IS_APPROVED)
            ->simplePaginate(5);

        $checkCount = 1;
        $this->assertTrue($checkCount == count($result->items()));
    }

    public function testAllowView()
    {
        $this->assertIsArray(ViewScope::getAllowView());
    }
}
