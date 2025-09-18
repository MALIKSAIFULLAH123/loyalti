<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Group;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupText;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class DeleteTest extends TestCase
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
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);

        $group = Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $repository->deleteGroup($user, $group->entityId());

        $this->assertEmpty(Group::query()->find($group->entityId()));
        $this->assertEmpty(GroupText::query()->find($group->entityId()));
    }
}
