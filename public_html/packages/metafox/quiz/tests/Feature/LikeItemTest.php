<?php

namespace MetaFox\Quiz\Tests\Feature;

use MetaFox\Like\Models\Like;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz;
use Tests\TestCase;

class LikeItemTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreateInstance(): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item = Quiz::factory()->setUser($user)->setOwner($user)->create();

        $this->assertInstanceOf(HasTotalLike::class, $item);
        $this->assertSame(0, $item->total_like);

        return [$user, $user2, $item];
    }

    /**
     * @depends testCreateInstance
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     */
    public function testLikeItem(array $data): array
    {
        [, $user2, $item] = $data;

        $like = Like::factory()
            ->setUser($user2)
            ->setOwner($user2)
            ->setItem($item)
            ->create();

        $item->refresh();

        $this->assertSame(1, $item->total_like);

        return [$user2, $item, $like];
    }

    /**
     * @depends testLikeItem
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     */
    public function testUnlikeItem(array $data): array
    {
        [, $item, $like] = $data;

        $like->delete();

        $item->refresh();

        $this->assertSame(0, $item->total_like);

        return $data;
    }
}
