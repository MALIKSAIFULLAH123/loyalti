<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Request;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Repositories\Eloquent\RequestRepository;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\User\Support\Facades\UserValue;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class AcceptMemberRequestTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(RequestRepositoryInterface::class);

        $this->expectNotToPerformAssertions();

        return $repository;
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws ValidatorException
     */
    public function testSuccess(RequestRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $request = Request::factory()->setUser($user2)->setOwner($group)->create();

        $this->markTestIncomplete();
        $repository->acceptMemberRequest($user, $group->entityId(), $user2->entityId());

        $memberExist = $repository->getModel()->newQuery()
            ->where('group_id', $group->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->assertTrue($memberExist);

        $request->refresh();

        $this->assertTrue(Request::STATUS_APPROVED == $request->status_id);
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function testValidate(RequestRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->markTestIncomplete();

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $this->expectException(ValidationException::class);
        $repository->acceptMemberRequest($user, $group->entityId(), $user2->entityId());
    }

    /**
     * @depends testInstance
     * @return array<int,                                 mixed>
     * @throws AuthorizationException|ValidationException
     * @throws ValidatorException
     */
    public function testGroupSecretWithModerator(RequestRepositoryInterface $repository): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user3 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->markTestIncomplete();
        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        Member::factory()->setUser($user3)->setOwner($group)->setModerator()->create();

        $request = Request::factory()->setUser($user2)->setOwner($group)->create();

        $this->assertTrue(Request::STATUS_PENDING == $request->refresh()->status_id);
        $repository->acceptMemberRequest($user3, $group->entityId(), $user2->entityId());
        $this->assertTrue(Request::STATUS_APPROVED == $request->refresh()->status_id);

        return [$user3, $group, $repository];
    }

    /**
     * @depends testGroupSecretWithModerator
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException|ValidationException
     * @throws ValidatorException
     */
    public function testWithModeratorNotPermission(array $data)
    {
        /**
         * @var User                       $user
         * @var Group                      $group
         * @var RequestRepositoryInterface $repository
         */
        [$user, $group, $repository] = $data;
        $user2                       = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $request = Request::factory()->setUser($user2)->setOwner($group)->create();

        $this->assertTrue(Request::STATUS_PENDING == $request->refresh()->status_id);

        UserValue::updateUserValueSetting($group, ['approve_or_deny_membership_request' => 0]);

        $this->expectException(AuthorizationException::class);
        $repository->acceptMemberRequest($user, $group->entityId(), $user2->entityId());
    }
}
