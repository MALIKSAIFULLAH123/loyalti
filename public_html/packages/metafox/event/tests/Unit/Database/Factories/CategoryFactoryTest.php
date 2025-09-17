<?php

namespace MetaFox\Event\Tests\Unit\Database\Factories;

use MetaFox\Event\Models\Category;
use Tests\TestCase;

/**
 * Class CategoryFactoryTest.
 */
class CategoryFactoryTest extends TestCase
{
    public function testCreateCategory()
    {
        $category = Category::factory()->create();

        $this->assertInstanceOf(Category::class, $category);
        $this->assertNotEmpty($category->name);
    }
}
