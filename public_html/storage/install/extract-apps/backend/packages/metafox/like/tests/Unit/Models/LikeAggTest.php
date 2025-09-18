<?php

namespace MetaFox\Like\Tests\Unit\Models;

use MetaFox\Like\Models\LikeAgg;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class LikeAggTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreate()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = ContentModel::factory()->setUser($user)->setOwner($user)->create();

        $model = LikeAgg::factory()->setItem($item)->create();

        $model->refresh();

        $this->assertNotEmpty($model->entityId());

        $reaction = $model->reaction;

        $this->assertEquals('preaction', $reaction->entityType());
    }
}

// end
