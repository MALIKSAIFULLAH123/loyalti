<?php

namespace MetaFox\Like\Tests\Feature;

use MetaFox\Like\Models\Like;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class LikeItemTest extends TestCase
{
    public function testCreateInstance(): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item = ContentModel::factory()->setUser($user)->setOwner($user)->create();

        $this->assertInstanceOf(HasTotalLike::class, $item);
        $this->assertSame(0, $item->total_like);

        return [$user, $item];
    }

    /**
     * @depends testCreateInstance
     *
     * @param array<mixed> $params
     */
    public function testLikeItem(array $params): array
    {
        /**
         * @var User    $user
         * @var Content $item
         */
        [$user, $item] = $params;

        $like = Like::factory()->setUser($user)->setItem($item)->create();

        $item->refresh();

        $this->assertSame(1, $item->total_like);

        return [$user, $item, $like];
    }

    /**
     * @depends testLikeItem
     *
     * @param array<mixed> $params
     */
    public function testUnLikeItem(array $params)
    {
        /**
         * @var User    $user
         * @var Content $item
         * @var Like    $like
         */
        [$user, $item, $like] = $params;

        $like->delete();

        $item->refresh();

        $this->assertSame(0, $item->total_like);
    }
}
