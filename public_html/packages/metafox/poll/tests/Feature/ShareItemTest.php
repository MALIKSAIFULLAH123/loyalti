<?php

namespace MetaFox\Poll\Tests\Feature;

use MetaFox\Activity\Models\Share;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Poll;
use Tests\TestCase;

class ShareItemTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreateInstance(): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item = Poll::factory()->setUser($user)->setOwner($user)->create();

        $this->assertInstanceOf(HasTotalShare::class, $item);
        $this->assertSame(0, $item->total_share);

        return [$user, $item];
    }

    /**
     * @depends testCreateInstance
     *
     * @param array<int, mixed> $params
     */
    public function testShareItem(array $params): array
    {
        [$user, $item] = $params;

        $share = Share::factory()
            ->setUser($user)
            ->setOwner($user)
            ->setItem($item)
            ->create();

        $item->refresh();

        $this->assertSame(1, $item->total_share);

        return [$user, $item, $share];
    }

    /**
     * @depends testShareItem
     *
     * @param array<int, mixed> $params
     */
    public function testDeleteShareItem(array $params)
    {
        [$user, $item, $share] = $params;

        $share->delete();

        $item->refresh();

        $this->assertSame(0, $item->total_share);
    }
}
