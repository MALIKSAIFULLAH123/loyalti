<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\PageMember;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\Eloquent\PageMemberRepository;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ActionUpdateTest extends TestCase
{
    protected PageMemberRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createNormalUser();
        $this->actingAs($this->user);
        $this->skipPolicies(PagePolicy::class);
        $this->repository = resolve(PageMemberRepositoryInterface::class);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(PageMemberRepository::class, $this->repository);
    }

    /**
     * @throws AuthorizationException
     */
    public function testSuccess()
    {
        $user = $this->user;

        $category = Category::factory()->create();

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $user2 = $this->createNormalUser();

        $pageMember = PageMember::factory()->setUser($user2)->setOwner($page)->create(['member_type' => PageMember::ADMIN]);
        $this->repository->updatePageMember($user, $page->entityId(), $user2->entityId(), PageMember::MEMBER);

        $pageMember->refresh();

        $this->assertTrue(PageMember::MEMBER == $pageMember->member_type);
    }
}
