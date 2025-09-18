<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\Page;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Group\Support\Browse\Scopes\Group\SortScope;
use MetaFox\Group\Support\Browse\Scopes\Group\ViewScope;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewAnyTest extends TestCase
{
    protected PageRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(PageRepositoryInterface::class);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(PageRepository::class, $this->repository);
    }

    /**
     * @throws AuthorizationException
     */
    public function testSuccess()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        $category = Category::factory()->create();

        Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
                'is_approved' => 1,
            ]);

        $params = [
            'q'           => '',
            'view'        => ViewScope::VIEW_DEFAULT,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'user_id'     => 0,
            'type_id'     => 0,
            'limit'       => 20,
        ];

        $result = $this->repository->viewPages($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @throws AuthorizationException
     */
    public function testSearch()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        $name = $this->faker->name;

        $category = Category::factory()->create();

        Page::factory()
            ->setUser($user)
            ->create([
                'name'        => $name,
                'category_id' => $category->entityId(),
                'is_approved' => 1,
            ]);

        $params = [
            'q'           => $name,
            'view'        => ViewScope::VIEW_DEFAULT,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'user_id'     => 0,
            'limit'       => 20,
        ];

        $result = $this->repository->viewPages($user2, $user, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @throws AuthorizationException
     */
    public function testByCategory()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();

        $this->actingAs($user);
        Page::factory()->setUser($user)
            ->create([
                'is_approved' => 1,
                'category_id' => $category->entityId(),
            ]);

        $params = [
            'q'           => '',
            'view'        => ViewScope::VIEW_DEFAULT,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => $category->entityId(),
            'user_id'     => 0,
            'limit'       => 20,
        ];

        $result = $this->repository->viewPages($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @throws AuthorizationException
     */
    public function testByType()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        $category = Category::factory()->create();

        Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $params = [
            'q'           => '',
            'view'        => ViewScope::VIEW_DEFAULT,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'user_id'     => 0,
            'limit'       => 20,
        ];

        $result = $this->repository->viewPages($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @throws AuthorizationException
     */
    public function testByProfile()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);
        $category = Category::factory()->create();

        Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

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

        $result = $this->repository->viewPages($user2, $user, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @throws AuthorizationException
     */
    public function testViewPending()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($user);

        $category = Category::factory()->create();

        Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
                'is_approved' => 0,
            ]);

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

        $result = $this->repository->viewPages($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @throws AuthorizationException
     */
    public function testViewFeature()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($user);

        $category = Category::factory()->create();

        Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
                'is_featured' => 1,
            ]);

        $params = [
            'q'           => '',
            'view'        => Browse::VIEW_FEATURE,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'user_id'     => 0,
            'limit'       => 20,
        ];

        $result = $this->repository->viewPages($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }

    /**
     * @throws AuthorizationException
     */
    public function testViewSponsor()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($user);

        $category = Category::factory()->create();

        Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
                'is_sponsor'  => 1,
            ]);

        $params = [
            'q'           => '',
            'view'        => Browse::VIEW_SPONSOR,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'user_id'     => 0,
            'limit'       => 20,
        ];

        $result = $this->repository->viewPages($user2, $user2, $params);
        $this->assertTrue($result->isNotEmpty());
    }
}
