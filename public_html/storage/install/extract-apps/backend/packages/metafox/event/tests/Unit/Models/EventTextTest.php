<?php

namespace MetaFox\Event\Tests\Unit\Models;

use MetaFox\Event\Models\EventText;
use Tests\TestCases\TestResourceTextModel;

/**
 * @group resource.text
 */
class EventTextTest extends TestResourceTextModel
{
    public function modelName(): string
    {
        return EventText::class;
    }
}
