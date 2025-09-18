<?php

namespace MetaFox\Video\Tests\Unit\Repositories\Eloquent;

use MetaFox\Video\Models\Category;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;
use Tests\TestCases\TestCategoryRepository;

class CategoryRepositoryTest extends TestCategoryRepository
{
    public function resourceName(): string
    {
        return Video::class;
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

// end
