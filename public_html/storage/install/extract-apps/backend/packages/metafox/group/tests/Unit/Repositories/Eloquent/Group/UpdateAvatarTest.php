<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Group;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class UpdateAvatarTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(GroupRepositoryInterface::class);
        $this->assertInstanceOf(GroupRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testSuccess(GroupRepositoryInterface $repository)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);
        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $repository->updateAvatar($user, $group->entityId(), $this->imageBase64);
        $group->refresh();

        $this->assertNotEmpty($group->avatar);
    }
}
