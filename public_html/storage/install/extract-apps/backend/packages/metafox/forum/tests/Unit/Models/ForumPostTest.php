<?php

namespace MetaFox\Forum\Tests\Unit\Models;

use MetaFox\Forum\Models\ForumPost;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class ForumPostTest extends TestContentModel
{
    public function modelName(): string
    {
        return ForumPost::class;
    }
}

// end
