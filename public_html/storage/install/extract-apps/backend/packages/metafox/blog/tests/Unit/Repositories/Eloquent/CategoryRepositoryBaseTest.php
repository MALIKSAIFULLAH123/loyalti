<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent;

use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Models\Category;
use MetaFox\Blog\Repositories\CategoryRepositoryInterface;
use Tests\TestCases\TestCategoryRepository;

class CategoryRepositoryBaseTest extends TestCategoryRepository
{
    public function resourceName(): string
    {
        return Blog::class;
    }

    public function repositoryName(): string
    {
        return CategoryRepositoryInterface::class;
    }

    public function categoryName(): string
    {
        return Category::class;
    }
}
