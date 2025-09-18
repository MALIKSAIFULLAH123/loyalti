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

class DeleteTest extends TestCase
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

        $rule = Rule::factory()->create(['group_id' => $group->entityId()]);

        return [$user, $rule, $repository];
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
         * @var RuleRepositoryInterface $repository
         */
        [$user, $rule, $repository] = $data;

        $repository->deleteRule($user, $rule->entityId());
        $this->assertEmpty(Rule::query()->find($rule->entityId()));
    }
}
