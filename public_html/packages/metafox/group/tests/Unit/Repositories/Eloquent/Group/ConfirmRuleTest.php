<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Group;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use Tests\TestCase;

class ConfirmRuleTest extends TestCase
{
    public function testInstance()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $user);

        $this->be($user);

        $group = Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $this->assertInstanceOf(Group::class, $group);

        return [$user, $group];
    }

    public function testRepositoryInstance()
    {
        $instance = resolve(GroupRepositoryInterface::class);

        $this->assertInstanceOf(GroupRepository::class, $instance);

        return $instance;
    }

    /**
     * @depends testInstance
     * @depends testRepositoryInstance
     */
    public function testConfirmRuleWithPublic(array $data, GroupRepositoryInterface $repository)
    {
        [$user, $group] = $data;

        $this->be($user);

        $isConfirmation = $this->faker->boolean;

        $repository->updateRuleConfirmation($user, $group->id, $isConfirmation);

        $group->refresh();

        $this->assertEquals($isConfirmation, $group->is_rule_confirmation);
    }
}
