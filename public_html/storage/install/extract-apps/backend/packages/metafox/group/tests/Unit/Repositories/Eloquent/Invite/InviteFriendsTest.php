<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Invite;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Repositories\Eloquent\InviteRepository;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class InviteFriendsTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $this->expectNotToPerformAssertions();
        $repository = resolve(InviteRepositoryInterface::class);
        $user       = $this->createNormalUser();
        $user1      = $this->createNormalUser();

        FriendFactory::new()->setUser($user1)->setOwner($user)->create();
        FriendFactory::new()->setUser($user)->setOwner($user1)->create();

        $group = Group::factory()->setUser($user)->setPrivacyType(PrivacyTypeHandler::PUBLIC)->create();

        return [$user, $user1, $group, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @return array<int,             mixed
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function testInviteFriendSuccess(array $data): array
    {
        /**
         * @var User                      $user
         * @var User                      $user1
         * @var Group                     $group
         * @var InviteRepositoryInterface $repository
         */
        [$user, $user1, $group, $repository] = $data;
        $repository->inviteFriends($user, $group->entityId(), [$user1->entityId()]);

        $invite = Invite::query()
            ->where('group_id', $group->entityId())
            ->where('owner_id', $user1->entityId())->first();

        $this->assertNotEmpty($invite);

        return [$user, $user1, $group, $repository, $invite];
    }

    /**
     * @depends testInviteFriendSuccess
     *
     * @param array<int, mixed> $data
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testInviteAlreadyMember(array $data): array
    {
        /**
         * @var User                      $user
         * @var User                      $user1
         * @var Group                     $group
         * @var InviteRepositoryInterface $repository
         * @var Invite                    $invite
         */
        [$user, $user1, $group, $repository, $invite] = $data;
        Member::factory()->setUser($user1)->setOwner($group)->create();
        $invite->update(['status_id' => Invite::STATUS_APPROVED]);

        $repository->inviteFriends($user, $group->entityId(), [$user1->entityId()]);

        $this->assertTrue($invite->refresh()->status_id == Invite::STATUS_APPROVED);

        return [$user, $user1, $group, $repository, $invite];
    }

    /**
     * @depends testInviteAlreadyMember
     *
     * @param array<int, mixed> $data
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testInviteAfterLeaveGroup(array $data): array
    {
        /**
         * @var User                      $user
         * @var User                      $user1
         * @var Group                     $group
         * @var InviteRepositoryInterface $repository
         * @var Invite                    $invite
         */
        [$user, $user1, $group, $repository, $invite] = $data;

        Member::query()->where('group_id', $group->entityId())
            ->where('user_id', $user1->entityId())->delete();

        $invite->update(['status_id' => Invite::STATUS_NOT_USE]);

        $repository->inviteFriends($user, $group->entityId(), [$user1->entityId()]);

        $this->assertTrue($invite->refresh()->status_id == Invite::STATUS_PENDING);

        return [$user, $user1, $group, $repository, $invite];
    }

    /**
     * @depends testInviteAfterLeaveGroup
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testAfterLeaveGroupNotInvite(array $data)
    {
        $this->markTestIncomplete();
        /**
         * @var User                      $user
         * @var User                      $user1
         * @var Group                     $group
         * @var InviteRepositoryInterface $repository
         * @var Invite                    $invite
         */
        [$user, $user1, $group, $repository, $invite] = $data;

        $invite->update(['status_id' => Invite::STATUS_NOT_INVITE_AGAIN]);

        $repository->inviteFriends($user, $group->entityId(), [$user1->entityId()]);

        $invite->refresh();

        $this->assertSame(Invite::STATUS_NOT_INVITE_AGAIN, $invite->status_id);
    }

    /**
     * @depends testInviteAlreadyMember
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function testInviteWhenHasRequest(array $data)
    {
        $this->markTestIncomplete();
        /**
         * @var User                      $user
         * @var User                      $user1
         * @var Group                     $group
         * @var InviteRepositoryInterface $repository
         * @var Invite                    $invite
         */
        [$user, , $group, $repository]   = $data;
        $user2                           = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $request = Request::factory()->setUser($user2)->setOwner($group)->create(['status_id' => Request::STATUS_PENDING]);
        $repository->inviteFriends($user, $group->entityId(), [$user2->entityId()]);

        $this->assertTrue($request->refresh()->status_id == Request::STATUS_APPROVED);
    }

    /**
     * @depends testInstance
     */
    public function testInviteNonFriendShouldNotBeProcessed(array $data)
    {
        $this->markTestIncomplete();
        [$user, , $group, $repository] = $data;

        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $repository->inviteFriends($user, $group->entityId(), [$user2->entityId()]);

        $invite = Invite::query()
        ->where('group_id', $group->entityId())
        ->where('owner_id', $user2->entityId())->first();

        $this->assertEmpty($invite);
        $this->assertNotInstanceOf(Invite::class, $invite);
    }
}
