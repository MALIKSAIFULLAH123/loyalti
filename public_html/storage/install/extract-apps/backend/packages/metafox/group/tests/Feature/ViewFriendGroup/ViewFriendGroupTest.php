<?php

namespace MetaFox\Group\Tests\Feature\ViewFriendGroup;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewFriendGroupTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(GroupRepositoryInterface::class);
        $this->assertInstanceOf(GroupRepository::class, $repository);
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        FriendFactory::new()->setUser($user)->setOwner($user2)->create();
        FriendFactory::new()->setUser($user2)->setOwner($user)->create();

        return [$user, $user2, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testSuccess(array $data)
    {
        /**
         * @var User                     $user
         * @var User                     $user2
         * @var GroupRepositoryInterface $repository
         */
        [$user, $user2, $repository] = $data;

        $group1 = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();
        $group2 = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)
            ->create();
        $group3 = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        $group4 = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        Request::factory()->setUser($user2)->setOwner($group4)->create(['status_id' => Request::STATUS_APPROVED]);
        Member::factory()->setUser($user2)->setOwner($group4)->create();

        $params = [
            'q'           => '',
            'view'        => Browse::VIEW_FRIEND,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'type_id'     => 0,
            'user_id'     => 0,
            'limit'       => 10,
        ];

        $results = $repository->viewGroups($user2, $user2, $params);
        $this->assertTrue($results->isNotEmpty());

        $resultsConverted = $this->convertForTest($results->items());
        $this->assertArrayHasKey($group1->entityId(), $resultsConverted);
        $this->assertArrayHasKey($group2->entityId(), $resultsConverted);
        $this->assertArrayHasKey($group4->entityId(), $resultsConverted);
        $this->assertArrayNotHasKey($group3->entityId(), $resultsConverted);
    }
}
