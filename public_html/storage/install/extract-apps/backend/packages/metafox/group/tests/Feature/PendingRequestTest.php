<?php

namespace MetaFox\Group\Tests\Feature;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\Eloquent\MemberRepository;
use MetaFox\Group\Repositories\Eloquent\RequestRepository;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Notification\Models\Notification;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use Tests\TestCase;

class PendingRequestTest extends TestCase
{
    public function testInstance()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $user);

        $group = Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)
            ->create();

        $this->assertInstanceOf(Group::class, $group);

        $repository = resolve(MemberRepositoryInterface::class);

        $this->assertInstanceOf(MemberRepository::class, $repository);

        return [$group, $repository, $user];
    }

    /**
     * @depends testInstance
     */
    public function testSendRequestToJoin(array $data)
    {
        [$group, $repository, $user] = $data;

        $context = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $context);

        $this->be($context);

        $result = $repository->createRequest($context, $group->entityId());

        $this->assertIsArray($result);

        $notification = Notification::factory()
            ->newModel()
            ->where([
                'type'            => 'group_pending_request',
                'notifiable_id'   => $user->entityId(),
                'notifiable_type' => $user->entityType(),
                'user_id'         => $context->entityId(),
                'user_type'       => $context->entityType(),
            ])
            ->first();

        $this->assertInstanceOf(Notification::class, $notification);

        return $context;
    }

    /**
     * @depends testInstance
     * @depends testSendRequestToJoin
     */
    public function testAcceptPendingRequest(array $data, User $pendingUser)
    {
        [$group, , $user] = $data;

        $this->be($user);

        $requestRepository = resolve(RequestRepositoryInterface::class);

        $this->assertInstanceOf(RequestRepository::class, $requestRepository);

        $result = $requestRepository->acceptMemberRequest($user, $group->entityId(), $pendingUser->entityId());

        $this->assertTrue($result);

        $notification = Notification::factory()
            ->newModel()
            ->where([
                'type'            => 'group_pending_request',
                'notifiable_id'   => $user->entityId(),
                'notifiable_type' => $user->entityType(),
                'user_id'         => $pendingUser->entityId(),
                'user_type'       => $pendingUser->entityType(),
            ])
            ->first();

        $this->assertNull($notification);

        $group->refresh();

        $this->assertEquals(2, $group->total_member);
    }
}
