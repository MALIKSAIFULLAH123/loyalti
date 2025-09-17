<?php

namespace MetaFox\Group\Tests\Unit\Models;

use MetaFox\Group\Models\Category;
use Tests\TestCases\TestCategoryModel;

class CategoryTest extends TestCategoryModel
{
    public function modelName(): string
    {
        return Category::class;
    }
}

// end
