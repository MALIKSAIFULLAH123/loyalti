<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\PageMember;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Repositories\Eloquent\PageMemberRepository;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class AddPageMemberTest extends TestCase
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
        $category = Category::factory()->create();

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $page->refresh();

        $totalMember = $page->total_member;

        $checkCount = 1;

        $result = $this->repository->addPageMember($page, $user2->entityId());
        $page->refresh();
        $this->assertTrue($result);

        $memberExist = $this->repository->getModel()->newQuery()
            ->where('page_id', $page->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->assertTrue($memberExist);
        $this->assertTrue(($page->total_member - $totalMember) == $checkCount);
    }

    /**
     * @throws ValidatorException
     */
    public function testFailed()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();

        $this->actingAs($user);

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $this->repository->addPageMember($page, $user2->entityId());
        $result = $this->repository->addPageMember($page, $user2->entityId());
        $this->assertTrue($result);
    }
}
