<?php

namespace MetaFox\Music\Tests\Unit\Models;

use MetaFox\Music\Models\Playlist;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class PlaylistTest extends TestContentModel
{
    public function modelName(): string
    {
        return Playlist::class;
    }
}
