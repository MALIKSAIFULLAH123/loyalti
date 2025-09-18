<?php

namespace MetaFox\Poll\Tests\Unit\Models;

use MetaFox\Poll\Models\Design;
use MetaFox\Poll\Models\Poll;
use Tests\TestCases\TestEntityModel;

class DesignTest extends TestEntityModel
{
    public function modelName(): string
    {
        return Design::class;
    }

    public function makeOne($user)
    {
        $this->actingAs($user);

        $poll = Poll::factory()
            ->setUser($user)
            ->setOwner($user)
            ->create(['privacy' => 0]);

        $id = $poll->getKey();

        return Design::find($id);
    }

    /**
     * @depends testFindById
     */
    public function testValidateStored($model)
    {
        $this->assertInstanceOf(Design::class, $model);
    }
}

// end
