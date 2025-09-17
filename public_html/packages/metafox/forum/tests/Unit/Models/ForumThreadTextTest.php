<?php

namespace MetaFox\Forum\Tests\Unit\Models;

use MetaFox\Forum\Models\ForumThreadText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class ForumThreadTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return ForumThreadText::class;
    }
}
