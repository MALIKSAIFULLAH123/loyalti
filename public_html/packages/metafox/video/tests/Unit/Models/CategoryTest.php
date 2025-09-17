<?php

namespace MetaFox\Video\Tests\Unit\Models;

use MetaFox\Video\Models\Category;
use Tests\TestCases\TestCategoryModel;

class CategoryTest extends TestCategoryModel
{
    public function modelName(): string
    {
        return Category::class;
    }
}

// end
