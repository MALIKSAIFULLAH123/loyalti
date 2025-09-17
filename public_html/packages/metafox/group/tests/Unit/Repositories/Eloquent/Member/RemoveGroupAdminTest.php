<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Member;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Repositories\Eloquent\MemberRepository;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RemoveGroupAdminTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(MemberRepositoryInterface::class);
        $this->assertInstanceOf(MemberRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testSuccess(MemberRepositoryInterface $repository): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        Member::factory()->setUser($user2)->setOwner($group)->setAdmin()->create();

        $group->refresh();
        $totalMember = $group->total_member;
        $this->assertTrue($group->isAdmin($user2));

        $result = $repository->removeGroupAdmin($user, $group->entityId(), $user2->entityId(), false);
        $group->refresh();
        $this->assertTrue($result);
        $this->assertTrue($totalMember == $group->total_member);
        $this->assertFalse($group->isAdmin($user2));
        $this->assertFalse($group->isModerator($user2));
        $this->assertTrue($group->isMember($user2));

        return [$user, $group, $repository];
    }

    /**
     * @depends testSuccess
     *
     * @param array<int, mixed> $data
     *
     * @return void
     * @throws AuthorizationException
     */
    public function testDeleteAdmin(array $data)
    {
        /**
         * @var User                      $user
         * @var Group                     $group
         * @var MemberRepositoryInterface $repository
         */
        [$user, $group, $repository] = $data;

        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        Member::factory()->setUser($user2)->setOwner($group)->setAdmin()->create();

        $group->refresh();
        $totalMember = $group->total_member;
        $this->assertTrue($group->isAdmin($user2));
        $checkCount = 1;

        $result = $repository->removeGroupAdmin($user, $group->entityId(), $user2->entityId(), true);
        $group->refresh();
        $this->assertTrue($result);
        $this->assertTrue(($totalMember - $group->total_member) == $checkCount);

        $this->assertFalse($group->isMember($user2));
    }

    /**
     * @depends testSuccess
     *
     * @param array<int, mixed> $data
     *
     * @return void
     * @throws AuthorizationException
     */
    public function testIsNotAdmin(array $data)
    {
        /**
         * @var User                      $user
         * @var Group                     $group
         * @var MemberRepositoryInterface $repository
         */
        [$user, $group, $repository] = $data;
        $user2                       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        Member::factory()->setUser($user2)->setOwner($group)->create();

        $this->expectException(HttpException::class);
        $repository->removeGroupAdmin($user, $group->entityId(), $user2->entityId(), true);
    }
}
