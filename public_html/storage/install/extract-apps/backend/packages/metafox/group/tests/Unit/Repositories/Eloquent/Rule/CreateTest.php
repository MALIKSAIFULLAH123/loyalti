<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Rule;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\Eloquent\RuleRepository;
use MetaFox\Group\Repositories\RuleRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class CreateTest extends TestCase
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

        return [$user, $group, $repository];
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
         * @var Group                   $group
         * @var RuleRepositoryInterface $repository
         */
        [$user, $group, $repository] = $data;

        $params = [
            'group_id'    => $group->entityId(),
            'title'       => $this->faker->title,
            'description' => $this->faker->text,
        ];

        $question = $repository->createRule($user, $params);
        $this->assertNotEmpty($question);
    }
}
