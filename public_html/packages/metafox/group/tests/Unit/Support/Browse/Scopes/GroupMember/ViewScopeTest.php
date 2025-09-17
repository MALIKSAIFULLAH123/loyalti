<?php

namespace MetaFox\Group\Tests\Unit\Support\Browse\Scopes\GroupMember;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\ViewScope;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewScopeTest extends TestCase
{
    protected MemberRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(MemberRepositoryInterface::class);
    }

    public function testInstance(): Group
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user3 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user4 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        Member::factory()->setUser($user2)
            ->setOwner($group)->create([
                'member_type' => Member::ADMIN,
            ]);

        Member::factory()->setUser($user3)
            ->setOwner($group)
            ->create([
                'member_type' => Member::MEMBER,
            ]);

        Request::factory()->setUser($user4)->setOwner($group)->create();

        $this->expectNotToPerformAssertions();

        return $group;
    }

    /**
     * @depends testInstance
     */
    public function testViewDefault(Group $group)
    {
        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_ALL)
            ->setGroupId($group->entityId());

        $result = $this->repository
            ->getModel()
            ->newQuery()
            ->addScope($viewScope)
            ->simplePaginate(20);

        $checkCount = 3;
        $this->assertTrue($checkCount == count($result->items()));
    }

    /**
     * @depends testInstance
     */
    public function testViewAdminOnly(Group $group)
    {
        $viewScope = new ViewScope();
        $viewScope->setView(ViewScope::VIEW_ADMIN)->setGroupId($group->entityId());

        $result = $this->repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(20);

        $checkCount = 2;
        $this->assertTrue($checkCount == count($result->items()));
    }

    /**
     * @depends testInstance
     */
    public function testViewMemberOnly(Group $group)
    {
        $viewScope = new ViewScope();
        $viewScope->setView(ViewScope::VIEW_MEMBER)
            ->setGroupId($group->entityId());

        $result = $this->repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(20);

        $checkCount = 1;
        $this->assertTrue($checkCount == count($result->items()));
    }

    /**
     * @depends testInstance
     */
    public function testViewPending(Group $group)
    {
        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_PENDING)
            ->setGroupId($group->entityId());

        $this->repository->getModel()
            ->newQuery()
            ->addScope($viewScope)->simplePaginate(20);

        $this->expectNotToPerformAssertions();
    }
}
