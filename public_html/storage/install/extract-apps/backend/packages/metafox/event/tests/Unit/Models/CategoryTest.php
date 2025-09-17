<?php

namespace MetaFox\Event\Tests\Unit\Models;

use MetaFox\Event\Models\Category;
use Tests\TestCases\TestCategoryModel;

class CategoryTest extends TestCategoryModel
{
    public function modelName(): string
    {
        return Category::class;
    }
}
