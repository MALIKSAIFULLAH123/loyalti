<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\PageMember;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Repositories\Eloquent\PageMemberRepository;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class RemovePageMemberTest extends TestCase
{
    protected PageMemberRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(PageMemberRepositoryInterface::class);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(PageMemberRepository::class, $this->repository);
    }

    public function testSuccess()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();

        $this->actingAs($user);

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        PageMember::factory()->setUser($user2)->setOwner($page)->create();

        $checkCount = 1;

        $page->refresh();
        $totalMember = $page->total_member;

        $result = $this->repository->removePageMember($page, $user2->entityId());
        $page->refresh();
        $this->assertTrue($result);
        $this->assertTrue(($totalMember - $page->total_member) == $checkCount);
    }

    public function testFailed()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        $category = Category::factory()->create();

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);
        $result = $this->repository->removePageMember($page, $user2->entityId());

        $this->assertFalse($result);
    }
}
