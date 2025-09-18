<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Member;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\Eloquent\MemberRepository;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class AddGroupMemberTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(MemberRepositoryInterface::class);
        $this->assertInstanceOf(MemberRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     * @throws ValidatorException
     */
    public function testSuccess(MemberRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $group->refresh();

        $totalMember = $group->total_member;

        $checkCount = 1;

        $result = $repository->addGroupMember($group, $user2->entityId());
        $group->refresh();
        $this->assertTrue($result);

        $memberExist = $repository->getModel()->newQuery()
            ->where('group_id', $group->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->assertTrue($memberExist);
        $this->assertTrue(($group->total_member - $totalMember) == $checkCount);
    }

    /**
     * @depends testInstance
     * @throws ValidatorException
     */
    public function testFailed(MemberRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $repository->addGroupMember($group, $user2->entityId());
        $result = $repository->addGroupMember($group, $user2->entityId());
        $this->assertFalse($result);
    }
}
