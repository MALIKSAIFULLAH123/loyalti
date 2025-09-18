<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent;

use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class PageInviteRepositoryTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(PageInviteRepositoryInterface::class);
        $this->assertInstanceOf(PageInviteRepositoryInterface::class, $repository);
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($user1)->setOwner($user)->create();
        FriendFactory::new()->setUser($user)->setOwner($user1)->create();

        $this->actingAs($user);
        $page = Page::factory()->setUser($user)->create();

        return [$user, $user1, $page, $repository];
    }

    /**
     * @depends testInstance
     */
    public function testInviteFriendShouldBeProcessed(array $data)
    {
        [$user, $user1, $page, $repository] = $data;

        $repository->inviteFriends($user, $page->entityId(), [$user1->entityId()]);

        $invite = PageInvite::query()
            ->where('page_id', $page->entityId())
            ->where('owner_id', $user1->entityId())->first();

        $this->assertNotEmpty($invite);
        $this->assertInstanceOf(PageInvite::class, $invite);
        $this->assertEquals(PageInvite::STATUS_PENDING, $invite->status_id);
    }

    /**
     * @depends testInstance
     */
    public function testInviteNonFriendShouldNotBeProcessed(array $data)
    {
        [$user,, $page, $repository] = $data;

        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $repository->inviteFriends($user, $page->entityId(), [$user2->entityId()]);

        $invite = PageInvite::query()
            ->where('page_id', $page->entityId())
            ->where('owner_id', $user2->entityId())->first();

        $this->assertEmpty($invite);
        $this->assertNotInstanceOf(PageInvite::class, $invite);
    }
}

// end
