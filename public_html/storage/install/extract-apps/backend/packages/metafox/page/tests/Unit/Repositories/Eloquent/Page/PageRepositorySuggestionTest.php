<?php

namespace Unit\Repositories\Eloquent\Page;

use MetaFox\Friend\Models\Friend;
use MetaFox\Page\Database\Factories\PageFactory;
use MetaFox\Page\Database\Factories\PageMemberFactory;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class PageRepositorySuggestionTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function testCreateInstance(): array
    {
        $service = resolve(PageRepositoryInterface::class);
        $this->assertInstanceOf(PageRepository::class, $service);

        $user   = $this->createNormalUser();
        $friend = $this->createNormalUser();

        Friend::factory()->setUser($user)->setOwner($friend)->create();
        Friend::factory()->setUser($friend)->setOwner($user)->create();

        $owner = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($owner);

        $page             = PageFactory::new()->setUser($owner)->create();
        $friendJoinedPage = PageFactory::new()->setUser($owner)->create();
        $joinedPage       = PageFactory::new()->setUser($owner)->create();
        $bothJoinedPage   = PageFactory::new()->setUser($owner)->create();

        // Friend like $friendJoinedPage
        PageMemberFactory::new()->setUser($friend)->setOwner($friendJoinedPage)->create();

        // Friend and user both like $bothJoinedPage
        PageMemberFactory::new()->setUser($friend)->setOwner($bothJoinedPage)->create();
        PageMemberFactory::new()->setUser($user)->setOwner($bothJoinedPage)->create();

        // You like $joinedPage
        PageMemberFactory::new()->setUser($user)->setOwner($joinedPage)->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(User::class, $friend);
        $this->assertInstanceOf(User::class, $owner);

        $this->assertInstanceOf(User::class, $page);
        $this->assertInstanceOf(User::class, $friendJoinedPage);
        $this->assertInstanceOf(User::class, $joinedPage);
        $this->assertInstanceOf(User::class, $bothJoinedPage);

        return [
            $service,
            $user, $friend, $owner,
            $page, $friendJoinedPage, $joinedPage, $bothJoinedPage,
        ];
    }

    /**
     * @depends testCreateInstance
     *
     * @param array<mixed> $params
     */
    public function testSuggestion(array $params)
    {
        [
            $service,
            $user, $friend, $owner,
            $page, $friendJoinedPage, $joinedPage, $bothJoinedPage,
        ] = $params;

        $data = $service->getSuggestion($user, [], false);
        $this->assertIsArray($data);

        $data = $this->convertForTest($data);

        $this->assertArrayHasKey($friendJoinedPage->entityId(), $data);
        $this->assertArrayNotHasKey($joinedPage->entityId(), $data);
        $this->assertArrayNotHasKey($bothJoinedPage->entityId(), $data);
        $this->assertArrayNotHasKey($page->entityId(), $data);
    }
}
