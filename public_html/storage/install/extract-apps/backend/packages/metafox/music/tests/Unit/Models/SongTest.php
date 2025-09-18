<?php

namespace MetaFox\Music\Tests\Unit\Models;

use MetaFox\Music\Models\Song;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class SongTest extends TestContentModel
{
    public function modelName(): string
    {
        return Song::class;
    }
}
