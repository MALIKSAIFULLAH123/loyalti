<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent\Poll;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Authorization\Models\Role;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Friend\Database\Factories\FriendListDataFactory;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Group\Database\Factories\GroupFactory;
use MetaFox\Group\Database\Factories\MemberFactory;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use Tests\TestCase;

class PollRepositoryCreatePollTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(PollRepositoryInterface::class);
        $this->assertInstanceOf(PollRepository::class, $repository);
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testCreatePoll()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        $params = [
            'question' => $this->faker->sentence,
            'answers'  => [
                ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 1],
                ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 2],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ];

        $repository = resolve(PollRepositoryInterface::class);
        $poll       = $repository->createPoll($user, $user, $params);
        $this->assertNotEmpty($poll->entityId());
        $this->assertTrue($poll->answers()->get()->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testCreatePollWithOwner()
    {
        $groupOwner = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $publicGroup = GroupFactory::new()->setUser($groupOwner)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        MemberFactory::new()->setUser($groupOwner)->setOwner($publicGroup)->setAdmin()->create();

        $params = [
            'question' => $this->faker->sentence,
            'answers'  => [
                ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 1],
                ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 2],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ];

        $repository = resolve(PollRepositoryInterface::class);
        $poll       = $repository->createPoll($groupOwner, $publicGroup, $params);
        $this->assertNotEmpty($poll->entityId());
        $this->assertTrue(($poll->ownerId() == $publicGroup->entityId()));
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testCreatePollWithPrivacyCustom()
    {
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($user1)->setOwner($user2)->create();
        FriendFactory::new()->setUser($user2)->setOwner($user1)->create();

        $list1 = FriendList::factory()->setUser($user1)->create();
        $list2 = FriendList::factory()->setUser($user1)->create();

        FriendListDataFactory::new(['list_id' => $list1->id])->setUser($user2)->create();
        FriendListDataFactory::new(['list_id' => $list2->id])->setUser($user2)->create();

        $params = [
            'question' => $this->faker->sentence,
            'answers'  => [
                ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 1],
                ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 2],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ];

        $repository = resolve(PollRepositoryInterface::class);
        $poll       = $repository->createPoll($user1, $user1, $params);
        $this->assertNotEmpty($poll->entityId());
    }

    /**
     * @throws AuthorizationException
     */
    public function testCreatePollWithoutPermissionAutoApprove()
    {
        /** @var Role $role */
        $role = Role::factory()->create();
        $role->givePermissionTo('poll.create');
        $user = $this->createUser()->assignRole($role);

        $params = [
            'question' => $this->faker->sentence,
            'answers'  => [
                ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 1],
                ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 2],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ];

        $repository = resolve(PollRepositoryInterface::class);
        $poll       = $repository->createPoll($user, $user, $params);
        $this->assertNotEmpty($poll->entityId());
        $this->assertFalse((bool) ($poll->is_approved));
    }
}

// end
