<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Models;

use MetaFox\BackgroundStatus\Models\BgsBackground;
use Tests\TestCase;

class BgsBackgroundTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testMakeOne()
    {
        /** @var BgsBackground $model */
        $model = BgsBackground::factory()->makeOne();

        $this->assertSame('pstatusbg_background', $model->entityType());

        $this->assertTrue($model->saveQuietly());
        $this->assertTrue($model->deleteQuietly());
    }
}

// end
