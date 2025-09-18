<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Rule;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Rule;
use MetaFox\Group\Repositories\Eloquent\RuleRepository;
use MetaFox\Group\Repositories\RuleRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewRulesTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(RuleRepositoryInterface::class);
        $this->assertInstanceOf(RuleRepository::class, $repository);
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        Rule::factory()->create(['group_id' => $group->entityId()]);

        return [$user, $group, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     */
    public function testSuccess(array $data)
    {
        /**
         * @var User                    $user
         * @var Group                   $group
         * @var RuleRepositoryInterface $repository
         */
        [$user, $group, $repository] = $data;
        $title                       = $this->faker->title;
        $params                      = [
            'group_id' => $group->entityId(),
            'limit'    => 10,
        ];

        $results = $repository->viewRules($user, $params);
        $this->assertTrue($results->isNotEmpty());
    }
}
