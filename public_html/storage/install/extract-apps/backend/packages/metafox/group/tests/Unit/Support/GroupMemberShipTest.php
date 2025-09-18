<?php

namespace MetaFox\Group\Tests\Unit\Support;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\Eloquent\MemberRepository;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\Membership;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class GroupMemberShipTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreateResource(): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $group = Group::factory()->setUser($user2)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)->create();

        $memberService = resolve(MemberRepositoryInterface::class);

        $this->assertNotEmpty($user);
        $this->assertNotEmpty($user2);
        $this->assertNotEmpty($group);
        $this->assertInstanceOf(MemberRepository::class, $memberService);

        return [$user, $user2, $group, $memberService];
    }

    /**
     * @depends testCreateResource
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function testJoinedMembership(array $data): array
    {
        /**
         * @var User                      $user
         * @var Group                     $group
         * @var MemberRepositoryInterface $memberService
         */
        [$user, , $group, $memberService] = $data;

        $memberService->createRequest($user, $group->entityId());
        $membership = Membership::getMembership($group, $user);

        $this->assertTrue(Membership::JOINED == $membership);

        return [$user, $group, $memberService];
    }

    /**
     * @depends testJoinedMembership
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testUnJoinMembership(array $data)
    {
        /**
         * @var User                      $user
         * @var Group                     $group
         * @var MemberRepositoryInterface $memberService
         */
        [$user, $group, $memberService] = $data;

        $this->actingAs($group->user);

        $memberService->unJoinGroup($user, $group->entityId(), false, null);
        $membership = Membership::getMembership($group, $user);

        $this->assertTrue(Membership::NO_JOIN == $membership);
    }

    /**
     * @depends testCreateResource
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function testRequestedMembership(array $data)
    {
        /**
         * @var User                      $user
         * @var User                      $user2
         * @var MemberRepositoryInterface $memberService
         */
        [$user, $user2, , $memberService] = $data;

        $group = Group::factory()->setUser($user2)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)->create();

        $memberService->createRequest($user, $group->entityId());
        $membership = Membership::getMembership($group, $user);

        $this->assertTrue(Membership::REQUESTED == $membership);
    }
}
