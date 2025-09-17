<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Invite;

use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Repositories\Eloquent\InviteRepository;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class HandleInviteLeaveGroupTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(InviteRepositoryInterface::class);
        $this->assertInstanceOf(InviteRepository::class, $repository);
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($user1)->setOwner($user)->create();
        FriendFactory::new()->setUser($user)->setOwner($user1)->create();
        $group  = Group::factory()->setUser($user)->setPrivacyType(PrivacyTypeHandler::PUBLIC)->create();
        $invite = Invite::factory()->setUser($user)->setOwner($user1)->create([
            'group_id'  => $group->entityId(),
            'status_id' => Invite::STATUS_APPROVED,
        ]);

        return [$user, $user1, $group, $repository, $invite];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     */
    public function testLeaveWithExistInvite(array $data)
    {
        /**
         * @var User                      $user
         * @var User                      $user1
         * @var Group                     $group
         * @var InviteRepositoryInterface $repository
         * @var Invite                    $invite
         */
        [$user, $user1, $group, $repository, $invite] = $data;
        $repository->handelInviteLeaveGroup($group->entityId(), $user1, false);

        $invite->refresh();

        $this->assertSame(Invite::STATUS_NOT_USE, $invite->status_id);
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     */
    public function testLeaveWithExistInviteNotInviteAgain(array $data)
    {
        /**
         * @var User                      $user
         * @var User                      $user1
         * @var Group                     $group
         * @var InviteRepositoryInterface $repository
         * @var Invite                    $invite
         */
        [, $user1, $group, $repository, $invite] = $data;
        $repository->handelInviteLeaveGroup($group->entityId(), $user1, true);

        $this->assertTrue($invite->refresh()->status_id == Invite::STATUS_NOT_INVITE_AGAIN);
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     */
    public function testLeaveWithNotExistInvite(array $data)
    {
        /**
         * @var Group                     $group
         * @var InviteRepositoryInterface $repository
         */
        $user                      = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        [, , $group, $repository]  = $data;
        $repository->handelInviteLeaveGroup($group->entityId(), $user, true);

        /** @var Invite $invite */
        $invite = Invite::query()
            ->where('owner_id', $user->entityId())
            ->where('group_id', $group->entityId())
            ->first();

        $this->assertNotEmpty($invite);
        $invite->refresh();
        $this->assertSame(Invite::STATUS_NOT_INVITE_AGAIN, $invite->status_id);
    }
}
