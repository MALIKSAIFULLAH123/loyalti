<?php

namespace MetaFox\Marketplace\Tests\Unit\Repositories\Eloquent;

use MetaFox\Marketplace\Models\Category;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use Tests\TestCases\TestCategoryRepository;

class CategoryRepositoryTest extends TestCategoryRepository
{
    public function resourceName(): string
    {
        return Listing::class;
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
