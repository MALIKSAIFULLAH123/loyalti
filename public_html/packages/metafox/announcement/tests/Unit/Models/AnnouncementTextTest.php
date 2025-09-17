<?php

namespace MetaFox\Announcement\Tests\Unit\Models;

use MetaFox\Announcement\Models\AnnouncementText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class AnnouncementTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return AnnouncementText::class;
    }
}
