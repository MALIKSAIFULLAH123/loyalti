<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Rule;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Rule;
use MetaFox\Group\Repositories\Eloquent\RuleRepository;
use MetaFox\Group\Repositories\RuleRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class OrderRuleTest extends TestCase
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

        $rule  = Rule::factory()->create(['group_id' => $group->entityId(), 'ordering' => 1]);
        $rule2 = Rule::factory()->create(['group_id' => $group->entityId(), 'ordering' => 2]);

        return [$user, $rule, $rule2, $repository];
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
         * @var User                    $user
         * @var Rule                    $rule
         * @var Rule                    $rule2
         * @var RuleRepositoryInterface $repository
         */
        [$user, $rule, $rule2, $repository] = $data;

        $order1 = 1;
        $order2 = 2;

        $params = [
            'orders' => [
                $rule->entityId()  => $order2,
                $rule2->entityId() => $order1,
            ],
            'group_id' => $rule->group_id,
        ];

        $repository->orderRules($user, $params);
        $this->assertSame($order2, $rule->refresh()->ordering);
        $this->assertSame($order1, $rule2->refresh()->ordering);
    }
}
