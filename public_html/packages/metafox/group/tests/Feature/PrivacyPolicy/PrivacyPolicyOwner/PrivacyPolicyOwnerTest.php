<?php

namespace MetaFox\Group\Tests\Feature\PrivacyPolicy\PrivacyPolicyOwner;

use MetaFox\Core\Repositories\PrivacyPolicyRepository;
use MetaFox\Group\Database\Factories\GroupFactory;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\PrivacyPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\User\Database\Factories\UserBlockedFactory;
use MetaFox\User\Support\Facades\UserBlocked;
use Tests\TestCase;

class PrivacyPolicyOwnerTest extends TestCase
{
    /**
     * @return PrivacyPolicy
     */
    public function testCreateInstance()
    {
        $repository = resolve(PrivacyPolicy::class);
        $this->assertInstanceOf(PrivacyPolicyRepository::class, $repository);

        return $repository;
    }

    /**
     * @param PrivacyPolicyRepository $repository
     *
     * @depends testCreateInstance
     * @return array<int, mixed>
     */
    public function testCreateResource(PrivacyPolicyRepository $repository): array
    {
        $user       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $groupOwner = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $publicGroup = GroupFactory::new()->setUser($groupOwner)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $closeGroup = GroupFactory::new()->setUser($groupOwner)
            ->setPrivacyType(PrivacyTypeHandler::CLOSED)
            ->create();

        $secretGroup = GroupFactory::new()->setUser($groupOwner)
            ->setPrivacyType(PrivacyTypeHandler::SECRET)
            ->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(User::class, $groupOwner);

        $this->assertInstanceOf(User::class, $publicGroup);
        $this->assertInstanceOf(User::class, $closeGroup);
        $this->assertInstanceOf(User::class, $secretGroup);

        return [$repository, $user, $groupOwner, $publicGroup, $closeGroup, $secretGroup];
    }

    /**
     * @param array<mixed> $params
     *
     * @depends testCreateResource
     *
     * @return array<int, mixed>
     */
    public function testGuestViewOwner(array $params): array
    {
        /**
         * @var PrivacyPolicyRepository $repository
         * @var User                    $user
         */
        [$repository, $user, $groupOwner, $publicGroup, $closeGroup, $secretGroup] = $params;
        $guestUser                                                                 = $this->createGuestUser();
        $this->assertInstanceOf(User::class, $guestUser);

        $this->assertTrue($repository->checkPermissionOwner($guestUser, $user));
        $this->assertTrue($repository->checkPermissionOwner($guestUser, $groupOwner));

        $this->assertTrue($repository->checkPermissionOwner($guestUser, $publicGroup));
        $this->assertTrue($repository->checkPermissionOwner($guestUser, $closeGroup));
        $this->assertFalse($repository->checkPermissionOwner($guestUser, $secretGroup));

        return [$repository, $user, $groupOwner, $publicGroup, $closeGroup, $secretGroup];
    }

    /**
     * @param array<mixed> $params
     *
     * @depends testGuestViewOwner
     */
    public function testOwnerBlockedUser(array $params)
    {
        /**
         * @var PrivacyPolicyRepository $repository
         */
        [$repository, $user, $groupOwner, $publicGroup, $closeGroup] = $params;

        UserBlocked::clearCache($user->entityId());
        UserBlocked::clearCache($groupOwner->entityId());

        UserBlockedFactory::new()->setUser($user)->setOwner($groupOwner)->create();
        $this->assertFalse($repository->checkPermissionOwner($user, $groupOwner));

        UserBlocked::clearCache($user->entityId());
        UserBlocked::clearCache($groupOwner->entityId());

        UserBlockedFactory::new()->setUser($user)->setOwner($publicGroup)->create();
        $this->assertFalse($repository->checkPermissionOwner($user, $publicGroup));

        UserBlocked::clearCache($user->entityId());
        UserBlocked::clearCache($publicGroup->entityId());

        UserBlockedFactory::new()->setUser($user)->setOwner($closeGroup)->create();
        $this->assertFalse($repository->checkPermissionOwner($user, $closeGroup));

        UserBlocked::clearCache($user->entityId());
        UserBlocked::clearCache($closeGroup->entityId());
    }
}
