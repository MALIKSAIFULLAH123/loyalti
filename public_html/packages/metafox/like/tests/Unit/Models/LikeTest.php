<?php

namespace MetaFox\Like\Tests\Unit\Models;

use MetaFox\Like\Models\Like;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class LikeTest extends TestCase
{
    /**
     * @return Content
     */
    public function testLikeModel(): Content
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = ContentModel::factory()->setOwner($user)->setUser($user)->create();

        $like = Like::factory(['reaction_id' => 2])->setUser($user)->setItem($item)->create();

        $morphItem = $like->item;

        $this->assertEquals($item->entityType(), $morphItem->entityType());
        $this->assertEquals($item->entityId(), $morphItem->entityId());

        $morphUser = $like->user;

        $this->assertEquals('user', $morphUser->entityType());

        $this->assertEquals($user->entityId(), $morphUser->entityId());

        $this->assertEquals(2, $like->reaction_id);

        $reaction = $like->reaction;

        $this->assertEquals('preaction', $reaction->entityType());

        return $item;
    }

    /**
     * @depends testLikeModel
     *
     * @param mixed $item
     */
    public function testLikeIntegration($item)
    {
        $item->refresh();

        $this->assertEquals(1, $item->total_like);

        $this->assertEquals(1, $item->likes()->count());
    }

    public function testPolicy()
    {
        $policy = PolicyGate::getPolicyFor(Like::class);
        $this->assertNotNull($policy);
    }
}
