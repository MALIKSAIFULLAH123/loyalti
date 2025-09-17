<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Request;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Repositories\Eloquent\MemberRepository;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class DenyMemberRequestTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(MemberRepositoryInterface::class);
        $this->assertInstanceOf(MemberRepository::class, $repository);

        $this->markTestIncomplete();

        return $repository;
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function testSuccess(MemberRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $request = Request::factory()->setUser($user2)->setOwner($group)->create();

        $repository->denyMemberRequest($user, $group->entityId(), $user2->entityId());

        $request->refresh();

        $this->assertTrue(Request::STATUS_DENIED == $request->status_id);
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testValidate(MemberRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $this->expectException(ValidationException::class);
        $repository->denyMemberRequest($user, $group->entityId(), $user2->entityId());
    }
}
