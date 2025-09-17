<?php

namespace MetaFox\Quiz\Tests\Unit\Models;

use MetaFox\Quiz\Models\QuizText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class QuizTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return QuizText::class;
    }
}
