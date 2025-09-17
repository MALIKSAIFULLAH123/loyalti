<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Models;

use MetaFox\BackgroundStatus\Models\BgsCollection;
use Tests\TestCase;

class BgsCollectionTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testMakeOne()
    {
        /** @var BgsCollection $model */
        $model = BgsCollection::factory()->makeOne();

        $this->assertSame('pstatusbg_collection', $model->entityType());

        $this->assertTrue($model->saveQuietly());
        $this->assertTrue($model->deleteQuietly());
    }
}

// end
