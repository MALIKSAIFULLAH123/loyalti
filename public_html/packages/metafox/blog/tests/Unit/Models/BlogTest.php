<?php

namespace MetaFox\Blog\Tests\Unit\Models;

use MetaFox\Blog\Models\Blog;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class BlogTest extends TestContentModel
{
    public function modelName(): string
    {
        return Blog::class;
    }
}
