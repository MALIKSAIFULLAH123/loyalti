<?php

namespace MetaFox\Forum\Tests\Unit\Models;

use MetaFox\Forum\Models\ForumThread;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class ForumThreadTest extends TestContentModel
{
    public function modelName(): string
    {
        return ForumThread::class;
    }
}

// end
