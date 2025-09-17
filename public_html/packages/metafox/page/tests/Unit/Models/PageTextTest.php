<?php

namespace MetaFox\Page\Tests\Unit\Models;

use MetaFox\Page\Models\PageText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class PageTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return PageText::class;
    }
}

// end
