<?php

namespace MetaFox\Blog\Tests\Unit\Models;

use MetaFox\Blog\Models\Category;
use Tests\TestCases\TestCategoryModel;

class CategoryTest extends TestCategoryModel
{
    public function modelName(): string
    {
        return Category::class;
    }
}

// end
