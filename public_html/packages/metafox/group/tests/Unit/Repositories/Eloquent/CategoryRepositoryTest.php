<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractCategoryRepository as RepositoryInterface;
use Tests\TestCases\TestCategoryRepository;

class CategoryRepositoryTest extends TestCategoryRepository
{
    public function resourceName(): string
    {
        return Group::class;
    }

    public function repositoryName(): string
    {
        return CategoryRepositoryInterface::class;
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
