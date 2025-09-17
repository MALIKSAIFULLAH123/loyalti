<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent\Group;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\Group\ViewScope;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\UserRole;
use MetaFox\User\Support\Facades\User;
use Tests\TestCase;

class ViewAnyTest extends TestCase
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
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $params = [
            'q'           => '',
            'view'        => ViewScope::VIEW_DEFAULT,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'type_id'     => 0,
            'user_id'     => 0,
            'limit'       => 20,
        ];

        $result = $repository->viewGroups($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testSearch(GroupRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $name = $this->faker->name;
        Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create([
                'name' => $name,
            ]);

        $params = [
            'q'           => $name,
            'view'        => ViewScope::VIEW_DEFAULT,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'type_id'     => 0,
            'user_id'     => 0,
            'limit'       => 20,
        ];

        $result = $repository->viewGroups($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testByCategory(GroupRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $groupCategory = Category::factory()->create();

        Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create([
                'category_id' => $groupCategory->entityId(),
            ]);

        $params = [
            'q'           => '',
            'view'        => ViewScope::VIEW_DEFAULT,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => $groupCategory->entityId(),
            'type_id'     => 0,
            'limit'       => 20,
            'user_id'     => 0,
        ];

        $result = $repository->viewGroups($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testByProfile(GroupRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $params = [
            'q'           => '',
            'view'        => ViewScope::VIEW_DEFAULT,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'type_id'     => 0,
            'user_id'     => $user->entityId(),
            'limit'       => 20,
        ];

        $result = $repository->viewGroups($user2, $user, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewPending(GroupRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::ADMIN_USER);

        Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create(['is_approved' => 0]);

        $params = [
            'q'           => '',
            'view'        => Browse::VIEW_PENDING,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'type_id'     => 0,
            'user_id'     => 0,
            'limit'       => 20,
        ];

        $result = $repository->viewGroups($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewFeature(GroupRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::ADMIN_USER);

        Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create(['is_featured' => 1]);

        $params = [
            'q'           => '',
            'view'        => Browse::VIEW_FEATURE,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'type_id'     => 0,
            'limit'       => 20,
            'user_id'     => 0,
        ];

        $result = $repository->viewGroups($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewSponsor(GroupRepositoryInterface $repository)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::ADMIN_USER);

        Group::factory()->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create(['is_sponsor' => 1]);

        $params = [
            'q'           => '',
            'view'        => Browse::VIEW_SPONSOR,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'type_id'     => 0,
            'user_id'     => 0,
            'limit'       => 20,
        ];

        $result = $repository->viewGroups($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @depends testInstance
     */
    public function testWithGuestUser(GroupRepositoryInterface $repository)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->setUser($user)
            ->create();

        $this->assertInstanceOf(Group::class, $group);

        $guest = User::getGuestUser();

        $this->assertInstanceOf(ContractUser::class, $guest);

        $this->assertInstanceOf(Authenticatable::class, $guest);

        $this->be($guest);

        $params = [
            'q'           => '',
            'view'        => Browse::VIEW_ALL,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'type_id'     => 0,
            'user_id'     => $guest->entityId(),
            'limit'       => 20,
        ];

        $result = $repository->viewGroups($guest, $guest, $params);

        $this->assertTrue($result->isNotEmpty());
    }
}
