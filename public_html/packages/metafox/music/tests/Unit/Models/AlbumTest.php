<?php

namespace MetaFox\Music\Tests\Unit\Models;

use MetaFox\Music\Models\Album;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class AlbumTest extends TestContentModel
{
    public function modelName(): string
    {
        return Album::class;
    }
}
