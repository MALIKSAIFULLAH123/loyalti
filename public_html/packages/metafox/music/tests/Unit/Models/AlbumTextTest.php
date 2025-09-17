<?php

namespace MetaFox\Music\Tests\Unit\Models;

use MetaFox\Music\Models\AlbumText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class AlbumTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return AlbumText::class;
    }
}
