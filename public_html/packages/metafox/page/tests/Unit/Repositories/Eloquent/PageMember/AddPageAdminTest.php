<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\PageMember;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\Eloquent\PageMemberRepository;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class AddPageAdminTest extends TestCase
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
     * @throws ValidatorException
     */
    public function testSuccess()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);
        $this->skipPolicies(PagePolicy::class);

        $category = Category::factory()->create();

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $page->refresh();

        $totalMember = $page->total_member;

        $checkCount = 1;

        // must be member first.
        $this->repository->addPageMember($page, $user2->getKey());
        $result = $this->repository->addPageAdmin($page, $user2->entityId());
        $page->refresh();
        $this->assertTrue($result);

        $memberExist = $this->repository->getModel()->newQuery()
            ->where('page_id', $page->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->assertNotEmpty($memberExist);
    }

    /**
     * @throws ValidatorException
     */
    public function testFailed()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);
        $this->skipPolicies(PagePolicy::class);

        $category = Category::factory()->create();

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);
        $this->repository->addPageAdmin($page, $user2->entityId());
        $result = $this->repository->addPageAdmin($page, $user2->entityId());
        $this->assertFalse($result);
    }

    /**
     * @throws ValidatorException
     * @throws AuthorizationException
     */
    public function testAddMultiSuccess()
    {
        $user     = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2    = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $category = Category::factory()->create();

        $this->actingAs($user);
        $this->skipPolicies(PagePolicy::class);

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $page->refresh();

        $totalMember = $page->total_member;

        $checkCount = 1;
        $this->repository->addPageMember($page, $user2->id);

        $result = $this->repository->addPageAdmins($user, $page->entityId(), [$user2->entityId()]);
        $page->refresh();
        $this->assertTrue($result);

        $memberExist = $this->repository->getModel()->newQuery()
            ->where('page_id', $page->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->assertTrue($memberExist);
    }
}
