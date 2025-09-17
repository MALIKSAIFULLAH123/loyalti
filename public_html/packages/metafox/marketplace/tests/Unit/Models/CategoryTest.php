<?php

namespace MetaFox\Marketplace\Tests\Unit\Models;

use MetaFox\Marketplace\Models\Category;
use Tests\TestCases\TestCategoryModel;

class CategoryTest extends TestCategoryModel
{
    public function modelName(): string
    {
        return Category::class;
    }
}
