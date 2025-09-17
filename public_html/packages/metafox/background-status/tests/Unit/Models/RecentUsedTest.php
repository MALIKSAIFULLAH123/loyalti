<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Models;

use Illuminate\Support\Facades\DB;
use MetaFox\BackgroundStatus\Models\RecentUsed;
use MetaFox\Platform\Contracts\User;
use Tests\TestCase;

class RecentUsedTest extends TestCase
{
    public function cleanDuplicated()
    {
        RecentUsed::query()->where(['user_id' => 1, 'background_id' => 1])->delete();
    }
    /**
     * A basic unit test example.
     *
     * @return int
     */
    public function testMakeOne()
    {
        $this->cleanDuplicated();

        /** @var RecentUsed $model */
        $model = RecentUsed::factory()->makeOne(['user_id' => 1, 'user_type' => 'user', 'background_id' => 1]);

        $this->assertSame('bgs_recent_used', $model->entityType());

        $this->assertTrue($model->saveQuietly());

        return $model->id;
    }

    /**
     * @param $id
     * @return void
     * @depends testMakeOne
     */
    public function testModelRelations($id)
    {
        $model  = RecentUsed::find($id);
        $id     = $model->id;

        /** @var RecentUsed $model */
        $model = RecentUsed::find($id);

        $this->assertInstanceOf(User::class, $model->user);

        $this->assertTrue($model->forceDelete());
    }
}

// end
