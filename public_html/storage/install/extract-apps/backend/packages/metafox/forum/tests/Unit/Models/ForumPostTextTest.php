<?php

namespace MetaFox\Forum\Tests\Unit\Models;

use MetaFox\Forum\Models\ForumPostText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class ForumPostTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return ForumPostText::class;
    }
}
