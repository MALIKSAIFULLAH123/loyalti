<?php

namespace MetaFox\Page\Tests\Unit\Models;

use Carbon\Carbon;
use MetaFox\Page\Models\PageInvite as Model;
use MetaFox\Platform\Contracts\User;
use Tests\TestCase;

class PageInviteTest extends TestCase
{
    public function testMakeOne()
    {
        /** @var Model $model */
        $model = Model::factory()->makeOne([
            'status_id'   => 1,
            'page_id'     => 1,
            'user_id'     => 1,
            'user_type'   => 'user',
            'owner_id'    => 1,
            'owner_type'  => 'user',
            'expired_at'  => Carbon::now()->addMonth(1),
            'invite_type' => '1',
        ]);

        $this->assertTrue($model->saveQuietly());

        $model->refresh();

        return $model->entityId();
    }

    /**
     * @param $id
     * @return Model
     * @depends testMakeOne
     */
    public function testFindById($id)
    {
        $model = Model::find($id);

        $this->assertNotEmpty($model);
        $this->assertSame($model->id, $id);

        return $model;
    }

    /**
     * @return Model
     * @depends testFindById
     * @return Model
     */
    public function testAttributtes(Model $model)
    {
        $this->assertNotEmpty($model);

        return $model;
    }

    /**
     * @param  Model $model
     * @return Model
     * @depends testAttributtes
     */
    public function testRelations(Model $model)
    {
        $this->assertInstanceOf(User::class, $model->user);
        $this->assertInstanceOf(User::class, $model->owner);

        return $model;
    }

    /**
     * @param  Model $model
     * @return Model
     * @depends testRelations
     */
    public function testUpdate(Model $model)
    {
        $params = [
            'status_id' => 0,
        ];

        $model->fill($params);
        $this->assertTrue($model->saveQuietly());

        $model->refresh();
        $this->assertSame($params['status_id'], $model->status_id);

        return $model;
    }

    /**
     * @param Model $model
     * @depends testUpdate
     */
    public function testDestroy(Model $model)
    {
        $this->assertTrue($model->forceDelete());
    }
}

// end
