<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Member;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Repositories\Eloquent\MemberRepository;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class UnJoinGroupTest extends TestCase
{
    public function testInstance()
    {
        $this->expectNotToPerformAssertions();

        $repository = resolve(MemberRepositoryInterface::class);

        return $repository;
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testSuccess(MemberRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $this->actingAs($group->user);

        Request::factory()->setUser($user2)->setOwner($group)->create(['status_id' => Request::STATUS_APPROVED]);

        Member::factory()->setUser($user2)->setOwner($group)->create();

        $repository->unJoinGroup($user2, $group->entityId(), false, null);

        $memberExist = $repository->getModel()->newQuery()
            ->where('group_id', $group->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->assertFalse($memberExist);
    }
}
