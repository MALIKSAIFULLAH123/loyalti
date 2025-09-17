<?php

namespace MetaFox\Announcement\Tests\Unit\Models;

use MetaFox\Announcement\Models\Style as Model;
use Tests\TestCase;

class StyleTest extends TestCase
{
    /**
     * @return void
     */
    public function testInstancePreSeeded()
    {
        $items = Model::query()->get();

        $this->assertTrue($items->isNotEmpty());
    }
}

// end
