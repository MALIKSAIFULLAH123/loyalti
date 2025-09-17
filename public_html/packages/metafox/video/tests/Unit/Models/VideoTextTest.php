<?php

namespace MetaFox\Page\Tests\Unit\Models;

use MetaFox\Video\Models\VideoText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class VideoTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return VideoText::class;
    }
}

// end
