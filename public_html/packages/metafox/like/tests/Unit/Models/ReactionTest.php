<?php

namespace MetaFox\Like\Tests\Unit\Models;

use MetaFox\Like\Models\Reaction;
use Tests\TestCases\TestEntityModel;

class ReactionTest extends TestEntityModel
{
    public function modelName(): string
    {
        return Reaction::class;
    }

    public function makeOne($user)
    {
        /** @var Reaction $model */
        $model = Reaction::factory()
            ->makeOne([
                'title'      => uniqid('sample title '),
                'is_active'  => 1,
                'icon_path'  => '/',
                'color'      => '#ccc',
                'server_id'  => 2,
                'ordering'   => 0,
                'is_default' => 0,
            ]);

        return $model;
    }

    /**
     * @return Reaction
     *
     * @depends testFindById
     */
    public function testValidateStored($model)
    {
        $this->assertNotEmpty($model->title);
        $this->assertSame(1, $model->is_active);
        $this->assertSame(0, $model->ordering);
        $this->assertSame('2', $model->server_id);

        return $model;
    }
}

// end
