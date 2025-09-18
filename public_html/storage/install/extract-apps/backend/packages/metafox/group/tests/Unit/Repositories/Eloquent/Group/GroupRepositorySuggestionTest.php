<?php

namespace Unit\Repositories\Eloquent\Group;

use MetaFox\Friend\Models\Friend;
use MetaFox\Group\Database\Factories\GroupFactory;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupRepositorySuggestionTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function testCreateInstance(): array
    {
        $service = resolve(GroupRepositoryInterface::class);
        $this->assertInstanceOf(GroupRepository::class, $service);

        $user   = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $friend = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Friend::factory()->setUser($user)->setOwner($friend)->create();
        Friend::factory()->setUser($friend)->setOwner($user)->create();

        $owner = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $publicAlreadyJoinedGroup = GroupFactory::new()->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $publicJoinedGroup = GroupFactory::new()->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $closedJoinedGroup = GroupFactory::new()->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)
            ->create();

        $secretJoinedGroup = GroupFactory::new()->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        $publicGroup = GroupFactory::new()->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $closedGroup = GroupFactory::new()->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)
            ->create();

        $secretGroup = GroupFactory::new()->setUser($owner)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        Member::factory()->setUser($friend)->setOwner($publicJoinedGroup)->create();
        Member::factory()->setUser($friend)->setOwner($publicAlreadyJoinedGroup)->create();
        Member::factory()->setUser($friend)->setOwner($closedJoinedGroup)->create();
        Member::factory()->setUser($friend)->setOwner($secretJoinedGroup)->create();

        Member::factory()->setUser($user)->setOwner($publicAlreadyJoinedGroup)->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(User::class, $friend);
        $this->assertInstanceOf(User::class, $owner);

        $this->assertInstanceOf(User::class, $publicJoinedGroup);
        $this->assertInstanceOf(User::class, $publicAlreadyJoinedGroup);
        $this->assertInstanceOf(User::class, $closedJoinedGroup);
        $this->assertInstanceOf(User::class, $secretJoinedGroup);

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(User::class, $friend);
        $this->assertInstanceOf(User::class, $owner);

        $this->assertInstanceOf(User::class, $publicGroup);
        $this->assertInstanceOf(User::class, $closedGroup);
        $this->assertInstanceOf(User::class, $secretGroup);

        return [
            $service, $user, $friend, $owner,
            $publicJoinedGroup, $publicAlreadyJoinedGroup, $closedJoinedGroup, $secretJoinedGroup,
            $publicGroup, $closedGroup, $secretGroup,
        ];
    }

    /**
     * @depends testCreateInstance
     *
     * @param array<mixed> $params
     */
    public function testSuggestion(array $params)
    {
        /**
         * @var GroupRepositoryInterface $service
         * @var User                     $user
         * @var User                     $friend
         * @var User                     $owner
         * @var User                     $publicJoinedGroup
         * @var User                     $publicAlreadyJoinedGroup
         * @var User                     $closedJoinedGroup
         * @var User                     $secretJoinedGroup
         * @var User                     $publicGroup
         * @var User                     $closedGroup
         * @var User                     $secretGroup
         */
        [
            $service, $user, $friend, $owner,
            $publicJoinedGroup, $publicAlreadyJoinedGroup, $closedJoinedGroup, $secretJoinedGroup,
            $publicGroup, $closedGroup, $secretGroup,
        ] = $params;

        $data = $service->getSuggestion($user, [], false);
        $this->assertIsArray($data);

        $data = $this->convertForTest($data);

        $this->assertArrayHasKey($publicJoinedGroup->entityId(), $data);
        $this->assertArrayNotHasKey($publicAlreadyJoinedGroup->entityId(), $data);

        $this->assertArrayNotHasKey($closedJoinedGroup->entityId(), $data);
        $this->assertArrayNotHasKey($secretJoinedGroup->entityId(), $data);

        $this->assertArrayNotHasKey($publicGroup->entityId(), $data);
        $this->assertArrayNotHasKey($closedGroup->entityId(), $data);
        $this->assertArrayNotHasKey($secretGroup->entityId(), $data);

        $this->assertIsArray($data);
    }
}
