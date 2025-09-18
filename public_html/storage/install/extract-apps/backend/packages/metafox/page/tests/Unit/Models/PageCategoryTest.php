<?php

namespace MetaFox\Page\Tests\Unit\Models;

use MetaFox\Page\Models\Category;
use Tests\TestCases\TestCategoryModel;

class PageCategoryTest extends TestCategoryModel
{
    public function modelName(): string
    {
        return Category::class;
    }
}

// end
