<?php

namespace MetaFox\Page\Tests\Unit\Support\Browse\Scopes\Page;

use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Browse\Scopes\Page\SortScope;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class SortScopeTest extends TestCase
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
     * @depends testInstance
     */
    public function testViewDefault()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();

        $this->actingAs($user);
        Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $viewScope = new SortScope();
        $viewScope->setSort(SortScope::SORT_MOST_MEMBER);

        $result = $this->repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(4);

        $this->assertNotEmpty($result->items());
    }
}
