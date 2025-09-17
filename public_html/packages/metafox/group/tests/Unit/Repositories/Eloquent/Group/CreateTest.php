<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Group;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class CreateTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(GroupRepositoryInterface::class);
        $this->assertInstanceOf(GroupRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     * @throws ValidatorException|AuthorizationException
     */
    public function testSuccess(GroupRepositoryInterface $repository)
    {
        $user = $this->createNormalUser();
        $this->be($user);

        $category = Category::factory()->create();

        $params = [
            'name'         => $this->faker->title,
            'privacy_type' => PrivacyTypeHandler::PUBLIC,
            'category_id'  => $category->entityId(),
        ];

        $group = $repository->createGroup($user, $user, $params);
        $group->refresh();
        $this->assertNotEmpty($group);
        $this->assertTrue($group->isAdmin($user));
    }
}
