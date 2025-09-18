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
use Tests\TestCase;

class AddGroupModeratorsTest extends TestCase
{
    public function testInstance(): array
    {
        $user  = $this->createNormalUser();
        $user2 = $this->createNormalUser();

        $group = Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        Member::factory()->setUser($user2)->setOwner($group)->create();

        $repository = resolve(MemberRepositoryInterface::class);

        $this->expectNotToPerformAssertions();

        return [$user, $user2, $group, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array $data
     *
     * @throws AuthorizationException
     */
    public function testSuccess(array $data)
    {
        /**
         * @var User                      $user
         * @var User                      $user2
         * @var Group                     $group
         * @var MemberRepositoryInterface $repository
         */
        [$user, $user2, $group, $repository] = $data;
        $group->refresh();

        $this->assertTrue($group->isMember($user2));
        $result = $repository->addGroupModerators($user, $group->entityId(), [$user2->entityId()]);
        $this->assertTrue($result);

        $memberExist = $repository->getModel()->newQuery()
            ->where('group_id', $group->entityId())
            ->where('user_id', $user2->entityId())
            ->where('member_type', Member::MODERATOR)
            ->exists();

        $this->markTestIncomplete();
        $this->assertTrue($memberExist);
        $this->assertFalse($group->isAdmin($user2));
        $this->assertTrue($group->isModerator($user2));
    }
}
