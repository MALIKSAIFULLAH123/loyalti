<?php

namespace MetaFox\Blog\Tests\Unit\Models;

use MetaFox\Blog\Models\BlogText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class BlogTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return BlogText::class;
    }
}
