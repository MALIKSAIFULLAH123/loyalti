<?php

namespace MetaFox\Page\Tests\Unit\Models;

use MetaFox\Page\Models\Page;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class PageTest extends TestContentModel
{
    public function modelName(): string
    {
        return Page::class;
    }
}
