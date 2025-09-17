<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Member;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Repositories\Eloquent\MemberRepository;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\ViewScope;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewGroupMembersTest extends TestCase
{
    public function testInstance(): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user3 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user4 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        Member::factory()->setUser($user2)->setOwner($group)->create();
        Member::factory()->setUser($user3)->setOwner($group)->setModerator()->create();
        Member::factory()->setUser($user4)->setOwner($group)->create();

        $repository = resolve(MemberRepositoryInterface::class);
        $this->assertInstanceOf(MemberRepository::class, $repository);

        return [$user, $user2, $user3, $user4, $group, $repository];
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
         * @var User                      $user3
         * @var User                      $user4
         * @var Group                     $group
         * @var MemberRepositoryInterface $repository
         */
        [, $user2, , $user4, $group, $repository] = $data;
        $checkCountMember                         = 2;
        $params                                   = [
            'q'     => '',
            'limit' => 10,
            'view'  => ViewScope::VIEW_MEMBER,
        ];

        $results = $repository->viewGroupMembers($user4, $group->entityId(), $params);
        $this->assertTrue($checkCountMember == count($results->items()));

        $params['view'] = ViewScope::VIEW_MODERATOR;

        $checkCountModerator = 1;
        $results             = $repository->viewGroupMembers($user4, $group->entityId(), $params);
        $this->assertTrue($checkCountModerator == count($results->items()));

        $params['view'] = ViewScope::VIEW_ADMIN;

        $checkCountAdmin = 1;
        $results         = $repository->viewGroupMembers($user4, $group->entityId(), $params);
        $this->assertTrue($checkCountAdmin == count($results->items()));

        $params['view'] = ViewScope::VIEW_MEMBER;
        $params['q']    = $user2->full_name;

        $checkCountSearch = 1;
        $results          = $repository->viewGroupMembers($user4, $group->entityId(), $params);
        $this->assertTrue($checkCountSearch == count($results->items()));
    }
}
