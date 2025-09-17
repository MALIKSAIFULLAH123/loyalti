<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent;

use MetaFox\Blog\Repositories\Eloquent\CategoryRepository as Repository;
use Tests\TestCases\TestRepository;

/**
 * @property \Mockery\MockInterface|Repository $repository;
 * @group repositories
 */
class CategoryRepositoryTest extends TestRepository
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createRepositoryPartialMock(Repository::class);
    }

    /**
     * @see \MetaFox\Blog\Repositories\Eloquent\CategoryRepository::viewCategory
     */
    public function testViewCategory()
    {
        $this->markTestIncomplete('coming soon!');
        $context = null;
        $id      = 1;
        $this->repository->viewCategory($context, $id);
    }

    /**
     * @see \MetaFox\Blog\Repositories\Eloquent\CategoryRepository::deleteCategory
     */
    public function testDeleteCategory()
    {
        $this->markTestIncomplete('coming soon!');
        $context       = null;
        $id            = 1;
        $newCategoryId = 1;
        $this->repository->deleteCategory($context, $id, $newCategoryId);
    }

    /**
     * @see \MetaFox\Blog\Repositories\Eloquent\CategoryRepository::moveToNewCategory
     */
    public function testMoveToNewCategory()
    {
        $this->markTestIncomplete('coming soon!');
        $category      = null;
        $newCategoryId = 1;
        $isDelete      = false;
        $this->repository->moveToNewCategory($category, $newCategoryId, $isDelete);
    }

    /**
     * @see \MetaFox\Blog\Repositories\Eloquent\CategoryRepository::deleteAllBelongTo
     */
    public function testDeleteAllBelongTo()
    {
        $this->markTestIncomplete('coming soon!');
        $category = null;
        $this->repository->deleteAllBelongTo($category);
    }
}
