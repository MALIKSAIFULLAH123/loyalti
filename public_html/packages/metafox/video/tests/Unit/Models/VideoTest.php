<?php
/**
 * @author developer@phpfox.com
 * @license phpfox.com
 */

namespace Tests\Video\Tests\Unit\Models;

use MetaFox\Video\Models\Video;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class VideoTest extends TestContentModel
{
    public function modelName(): string
    {
        return Video::class;
    }
}
