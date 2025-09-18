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

class ChangeToModeratorTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(MemberRepositoryInterface::class);

        $this->expectNotToPerformAssertions();

        return $repository;
    }

    /**
     * @depends testInstance
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testSuccess(MemberRepositoryInterface $repository): array
    {
        $user  = $this->createNormalUser();
        $user2 = $this->createNormalUser();

        $this->markTestIncomplete();
        $this->actingAs($user2);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        Member::factory()->setUser($user2)->setOwner($group)->setAdmin()->create();

        $this->assertTrue($group->isAdmin($user2));

        $result = $repository->changeToModerator($user, $group->entityId(), $user2->entityId());
        $this->assertTrue($result);
        $this->assertFalse($group->isAdmin($user2));
        $this->assertTrue($group->isModerator($user2));
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
        $repository->changeToModerator($user, $group->entityId(), $user2->entityId());
    }
}
