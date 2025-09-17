<?php

namespace MetaFox\Blog\Tests\Feature\Privacy;

use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Core\Support\Privacy\Traits\FetchPrivacy;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Friend\Database\Factories\FriendListDataFactory;
use MetaFox\Friend\Database\Factories\FriendListFactory;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class PrivacyStreamCustomTest extends TestCase
{
    use FetchPrivacy;

    public function testCreateInstance()
    {
        $context = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $friend  = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($context)->setOwner($friend)->create();
        FriendFactory::new()->setUser($friend)->setOwner($context)->create();

        $friendList1 = FriendListFactory::new()->setUser($context)->create();
        $friendList2 = FriendListFactory::new()->setUser($context)->create();

        FriendListDataFactory::new(['list_id' => $friendList1->entityId()])->setUser($friend)->create();

        $item = Blog::factory()->setUser($context)->setOwner($context)
            ->setCustomPrivacy([
                $friendList1->entityId(),
                $friendList2->entityId(),
            ])
            ->create([]);

        $this->assertInstanceOf(Content::class, $item);

        $friendList1Privacy = $this->getPrivacy($friendList1->entityId(), $friendList1->entityType());
        $this->assertSame(1, $friendList1Privacy->count());

        $friendList1PrivacyIds = array_keys($friendList1Privacy->keyBy('privacy_id')->toArray());

        $friendList2Privacy = $this->getPrivacy($friendList2->entityId(), $friendList2->entityType());
        $this->assertSame(1, $friendList2Privacy->count());

        $friendList2PrivacyIds = array_keys($friendList2Privacy->keyBy('privacy_id')->toArray());

        $friendListPrivacyIds = array_merge($friendList1PrivacyIds, $friendList2PrivacyIds);
        $this->assertCount(2, $friendListPrivacyIds);

        $itemPrivacyIds = $this->getPrivacyIdsFromStream($item->entityId(), $item->entityType());
        $this->assertCount(2, $itemPrivacyIds);

        $this->assertSame($friendListPrivacyIds, $itemPrivacyIds);
        $this->assertSame($this->getPrivacyIdsFromResourceStream($item), $itemPrivacyIds);

        resolve(BlogRepositoryInterface::class)->updateBlog($context, $item->entityId(), [
            'remove_image' => false,
            'temp_file'    => 0,
            'privacy'      => MetaFoxPrivacy::CUSTOM,
            'list'         => [$friendList1->entityId()],
        ]);

        $friendList1Privacy = $this->getPrivacy($friendList1->entityId(), $friendList1->entityType());
        $this->assertSame(1, $friendList1Privacy->count());

        $friendList1PrivacyIds = array_keys($friendList1Privacy->keyBy('privacy_id')->toArray());

        $friendList2Privacy = $this->getPrivacy($friendList2->entityId(), $friendList2->entityType());
        $this->assertSame(1, $friendList2Privacy->count());

        $friendList2PrivacyIds = array_keys($friendList2Privacy->keyBy('privacy_id')->toArray());
        $this->assertSame(1, $friendList2Privacy->count());
        $itemPrivacyIds = $this->getPrivacyIdsFromStream($item->entityId(), $item->entityType());

        $this->assertSame($friendList1PrivacyIds[0], $itemPrivacyIds[0]);
        $this->assertNotSame($friendList2PrivacyIds[0], $itemPrivacyIds[0]);
        $this->assertSame($this->getPrivacyIdsFromResourceStream($item), $itemPrivacyIds);
    }
}
