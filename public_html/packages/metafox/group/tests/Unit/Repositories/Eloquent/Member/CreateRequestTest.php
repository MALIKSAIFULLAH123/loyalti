<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Member;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Repositories\Eloquent\MemberRepository;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class CreateRequestTest extends TestCase
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
     * @throws AuthorizationException
     */
    public function testGroupPublicSuccess(MemberRepositoryInterface $repository)
    {
        $user  = $this->createNormalUser();
        $user2 = $this->createNormalUser();

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $repository->createRequest($user2, $group->entityId());

        $memberExist = $repository->getModel()->newQuery()
            ->where('group_id', $group->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->markTestIncomplete();

        $this->assertTrue($memberExist);

        /** @var Request $request */
        $request = Request::query()->where('group_id', $group->entityId())
            ->where('user_id', $user2->entityId())->first();

        $this->assertNotEmpty($request);
        $this->assertTrue(Request::STATUS_APPROVED == $request->status_id);
    }

    /**
     * @depends testInstance
     * @throws ValidatorException
     * @throws AuthorizationException
     */
    public function testJoinWhenHasInvite(MemberRepositoryInterface $repository)
    {
        $user  = $this->createNormalUser();
        $user2 = $this->createNormalUser();

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $invite = Invite::factory()->setUser($user)->setOwner($user2)->create([
            'group_id'  => $group->entityId(),
            'status_id' => Invite::STATUS_PENDING,
        ]);

        $repository->createRequest($user2, $group->entityId());

        $memberExist = $repository->getModel()->newQuery()
            ->where('group_id', $group->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->assertTrue($memberExist);
        $this->assertTrue($invite->refresh()->status_id == Invite::STATUS_APPROVED);
    }

    /**
     * @depends testInstance
     * @throws ValidatorException
     * @throws AuthorizationException
     */
    public function testGroupSecretSuccess(MemberRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)
            ->create();

        $repository->createRequest($user2, $group->entityId());

        $memberExist = $repository->getModel()->newQuery()
            ->where('group_id', $group->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->assertFalse($memberExist);

        /** @var Request $request */
        $request = Request::query()->where('group_id', $group->entityId())
            ->where('user_id', $user2->entityId())->first();

        $this->assertNotEmpty($request);
        $this->assertTrue(Request::STATUS_PENDING == $request->status_id);
    }
}
