<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Member;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Repositories\Eloquent\MemberRepository;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class RemoveGroupMemberTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(MemberRepositoryInterface::class);
        $this->assertInstanceOf(MemberRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     */
    public function testSuccess(MemberRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        Member::factory()->setUser($user2)->setOwner($group)->create();

        $checkCount = 1;

        $group->refresh();
        $totalMember = $group->total_member;

        $result = $repository->removeGroupMember($group, $user2->entityId());
        $group->refresh();
        $this->assertTrue($result);
        $this->assertTrue(($totalMember - $group->total_member) == $checkCount);
    }

    /**
     * @depends testInstance
     */
    public function testFailed(MemberRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $result = $repository->removeGroupMember($group, $user2->entityId());

        $this->assertFalse($result);
    }
}
