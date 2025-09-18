<?php

namespace MetaFox\Like\Tests\Unit\Listeners;

use MetaFox\Like\Listeners\ModelDeletingListener;
use MetaFox\Like\Models\Like;
use MetaFox\Like\Models\LikeAgg;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ModelDeletedListenerTest extends TestCase
{
    public function testDeleteItemSuccess()
    {
        $this->markTestIncomplete();
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();
        Like::factory()->setUser($user)->setItem($item)->create();

        (new ModelDeletingListener())->handle($item);

        $likes = Like::query()
            ->where('item_id', $item->entityId())
            ->where('item_type', $item->entityType())
            ->get();

        $likeAgg = LikeAgg::query()
            ->where('item_id', $item->entityId())
            ->where('item_type', $item->entityType())
            ->get();

        $this->assertEmpty($likes);
        $this->assertEmpty($likeAgg);
    }

    public function testDeleteUserSuccess()
    {
        $this->markTestIncomplete();
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item  = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();
        $item2 = ContentModel::factory()->setUser($user2)->setOwner($user2)->create();
        Like::factory()->setUser($user)->setItem($item)->create();
        Like::factory()->setUser($user)->setItem($item2)->create();

        (new ModelDeletingListener())->handle($user);

        $checkCount = 0;
        $likes      = Like::query()
            ->where('user_id', $user->entityId())
            ->where('user_type', $user->entityType())
            ->get();

        $this->assertTrue($checkCount == $item->refresh()->total_like);
        $this->assertTrue($checkCount == $item2->refresh()->total_like);

        $this->assertEmpty($likes);
    }
}
