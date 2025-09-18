<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractCategoryRepository as RepositoryInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCases\TestCategoryRepository;

class PageCategoryRepositoryTest extends TestCategoryRepository
{
    public function resourceName(): string
    {
        return Page::class;
    }

    public function repositoryName(): string
    {
        return PageCategoryRepositoryInterface::class;
    }

    public function categoryName(): string
    {
        return Category::class;
    }

    /**
     * @depends testRepository
     * @depends testInstance
     */
    public function testDeleteCategoryWithNewCategory(RepositoryInterface $repository, Model $category)
    {
        $this->markTestIncomplete();
    }

    /**
     * @depends testRepository
     * @depends testInstance
     */
    public function testDeleteCategoryRemoveAll(RepositoryInterface $repository)
    {
        $this->markTestIncomplete();
    }
}

// end
