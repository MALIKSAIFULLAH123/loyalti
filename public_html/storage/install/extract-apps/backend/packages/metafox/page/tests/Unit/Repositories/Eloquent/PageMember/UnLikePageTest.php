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

class UnLikePageTest extends TestCase
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

    /**
     * @throws AuthorizationException
     */
    public function testSuccess()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);
        $category = Category::factory()->create();

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        PageMember::factory()->setUser($user2)->setOwner($page)->create();

        $this->skipPolicies(PagePolicy::class);
        $this->repository->unLikePage($user2, $page->entityId());

        $memberExist = $this->repository->getModel()->newQuery()
            ->where('page_id', $page->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->assertFalse($memberExist);
    }
}
