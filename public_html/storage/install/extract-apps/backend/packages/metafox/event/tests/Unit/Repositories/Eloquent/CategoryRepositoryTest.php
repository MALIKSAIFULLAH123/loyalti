<?php

namespace MetaFox\Event\Tests\Unit\Repositories\Eloquent;

use MetaFox\Event\Models\Category;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\CategoryRepositoryInterface;
use Tests\TestCases\TestCategoryRepository;

/**
 * Class CategoryRepositoryCreateCategoryTest.
 */
class CategoryRepositoryTest extends TestCategoryRepository
{
    public function resourceName(): string
    {
        return Event::class;
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
