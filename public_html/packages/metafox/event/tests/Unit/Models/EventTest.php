<?php

namespace MetaFox\Event\Tests\Unit\Models;

use MetaFox\Event\Models\Event;
use Tests\TestCases\TestContentModel;

/**
 * @group resource.content
 */
class EventTest extends TestContentModel
{
    public function modelName(): string
    {
        return Event::class;
    }
}
