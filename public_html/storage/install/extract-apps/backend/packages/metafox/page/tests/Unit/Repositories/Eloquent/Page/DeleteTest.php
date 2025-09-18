<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\Page;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\PageText;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    protected PageRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(PageRepositoryInterface::class);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(PageRepository::class, $this->repository);
    }

    /**
     * @throws AuthorizationException
     */
    public function testSuccess()
    {
        $user     = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $category = Category::factory()->create();

        $this->actingAs($user);
        $this->skipPolicies(PagePolicy::class);
        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $this->repository->deletePage($user, $page->entityId());

        $this->assertEmpty(Page::query()->find($page->entityId()));
        $this->assertEmpty(PageText::query()->find($page->entityId()));
    }
}
