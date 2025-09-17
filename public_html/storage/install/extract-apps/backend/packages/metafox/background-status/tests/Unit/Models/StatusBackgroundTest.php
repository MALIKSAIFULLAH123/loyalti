<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Models;

use MetaFox\BackgroundStatus\Models\StatusBackground;
use Tests\TestCase;

class StatusBackgroundTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testMakeOne()
    {
        /** @var StatusBackground $model */
        $model = StatusBackground::factory()->makeOne([
            'item_id'       => 0,
            'item_type'     => 'blog',
            'user_id'       => 1,
            'user_type'     => 'user',
            'background_id' => 1,
        ]);

        $this->assertSame('bgs_status_background', $model->entityType());

        $this->assertTrue($model->saveQuietly());
        $this->assertTrue($model->deleteQuietly());
    }
}

// end
