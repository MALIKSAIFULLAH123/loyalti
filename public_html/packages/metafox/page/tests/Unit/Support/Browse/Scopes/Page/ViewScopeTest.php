<?php

namespace MetaFox\Page\Tests\Unit\Support\Browse\Scopes\Page;

use MetaFox\Friend\Models\Friend;
use MetaFox\Page\Database\Factories\PageInviteFactory;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Browse\Scopes\Page\ViewScope;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

/**
 * @todo: clean up testing style
 *      - Not using setUp
 *      - Not create instance on each test case
 */
class ViewScopeTest extends TestCase
{
    protected PageRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(PageRepositoryInterface::class);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(PageRepository::class, $this->repository);
    }

    /**
     * @return Model
     * @depends testInstance
     */
    public function testCreate(): Model
    {
        $user     = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $category = Category::factory()->create();

        $this->actingAs($user);

        $page = Model::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
                'is_approved' => 1,
            ]);

        $this->expectNotToPerformAssertions();

        return $page;
    }

    /**
     * @return Model
     * @depends testInstance
     */
    public function testCreatePending(): Model
    {
        $user     = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $category = Category::factory()->create();

        $this->actingAs($user);

        $page = Model::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
                'is_approved' => 0,
            ]);

        $this->expectNotToPerformAssertions();

        return $page;
    }

    /**
     * @depends testCreate
     */
    public function testViewDefault(Model $page)
    {
        $user = $page->user;

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_ALL)->setUserContext($user);

        $result = $this->repository
            ->getModel()
            ->newQuery()
            ->addScope($viewScope)->simplePaginate(4);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testCreate
     */
    public function testViewMy(Model $model)
    {
        $user = $model->user;

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_MY)->setUserContext($user);

        $result = $this->repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(10);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testCreate
     */
    public function testViewFiend(Model $model)
    {
        $user  = $model->user;
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();
        Friend::factory()->setUser($user)->setOwner($user2)->create();
        Friend::factory()->setUser($user2)->setOwner($user)->create();

        $checkCount = 2;

        $this->actingAs($user);

        Model::factory()->count($checkCount)->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        Model::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
                'is_approved' => 0,
            ]);

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_FRIEND)->setUserContext($user2);

        $result = $this->repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(20);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @depends testCreatePending
     */
    public function testViewPending(Model $model)
    {
        $user  = $model->user;
        $user2 = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($user);

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_PENDING)->setUserContext($user2);

        $result = $this->repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(5);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testCreate
     */
    public function testViewLike(Model $model)
    {
        $user = $model->user;

        $this->actingAs($user);

        $viewScope = new ViewScope();
        $viewScope->setView(ViewScope::VIEW_LIKED)
            ->setUserContext($user)
            ->setIsViewProfile(true);

        $result = $this->repository
            ->getModel()
            ->newQuery()
            ->addScope($viewScope)->simplePaginate(5);

        // actual not like ?
        $this->expectNotToPerformAssertions();
    }

    /**
     * @depends testCreate
     */
    public function testViewInvited(Model $model)
    {
        $user  = $model->user;
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);
        PageInviteFactory::new()->setPage($model)->setUser($user1)->setOwner($user)->create();

        $viewScope = new ViewScope();
        $viewScope->setView(ViewScope::VIEW_INVITED)->setUserContext($user1);

        $result = $this->repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(5);

        $this->markTestIncomplete();

        $checkCount = 1;
        $this->assertCount($checkCount, $result->items());
    }

    /**
     * @depends testCreate
     */
    public function testViewOnProfile(Model $model)
    {
        $user  = $model->user;
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        $viewScope = new ViewScope();
        $viewScope->setView(ViewScope::VIEW_LIKED)->setUserContext($user2)->setIsViewProfile(true);

        $result = $this->repository->getModel()->newQuery()
            ->addScope($viewScope)
            ->where('pages.user_id', '=', $user->entityId())
            ->where('pages.is_approved', Model::IS_APPROVED)
            ->simplePaginate(5);

        $this->markTestIncomplete();

        $checkCount = 1;
        $this->assertTrue($checkCount == count($result->items()));
    }
}
