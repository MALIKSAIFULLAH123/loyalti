<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Group;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GetGroupForMentionTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(GroupRepositoryInterface::class);
        $this->assertInstanceOf(GroupRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     *
     * @param GroupRepositoryInterface $repository
     *
     * @return array<int, mixed>
     */
    public function testSuccess(GroupRepositoryInterface $repository): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $group2 = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        $params = [
            'q'     => '',
            'limit' => 20,
        ];

        $results = $repository->getGroupForMention($user, $params);
        $this->assertTrue($results->isNotEmpty());

        $resultsConverted = $this->convertForTest($results->items());
        $this->assertArrayNotHasKey($group2->entityId(), $resultsConverted);

        return [$user, $repository];
    }

    /**
     * @depends testSuccess
     *
     * @param array<int, mixed> $data
     */
    public function testSuccessWithSearch(array $data)
    {
        /**
         * @var User                     $user
         * @var GroupRepositoryInterface $repository
         */
        [$user, $repository] = $data;
        $name                = 'venomTrMF';

        $group1 = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create(['name' => $name]);

        $group2 = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $params = [
            'q'     => 'venomTr',
            'limit' => 20,
        ];

        $results = $repository->getGroupForMention($user, $params);
        $this->assertTrue($results->isNotEmpty());

        $resultsConverted = $this->convertForTest($results->items());
        $this->assertArrayHasKey($group1->entityId(), $resultsConverted);
        $this->assertArrayNotHasKey($group2->entityId(), $resultsConverted);
    }
}
