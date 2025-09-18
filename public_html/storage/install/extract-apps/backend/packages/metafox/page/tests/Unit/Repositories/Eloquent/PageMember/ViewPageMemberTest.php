<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\PageMember;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Repositories\Eloquent\PageMemberRepository;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewPageMemberTest extends TestCase
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

        $category = Category::factory()->create();
        $this->actingAs($user);

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        PageMember::factory()->setUser($user2)->setOwner($page)->create();

        $params = [
            'q'     => '',
            'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
            'view'  => 'recent',
        ];

        $result = $this->repository->viewPageMembers($user, $page->entityId(), $params);

        $this->assertCount(1, $result);
    }

    /**
     * @throws AuthorizationException
     */
    public function testSearchSuccess()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $fullName = $this->faker->name;
        $user2    = $this->createUser([
            'full_name' => $fullName,
        ])->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        $user3 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        PageMember::factory()->setUser($user2)->setOwner($page)->create();
        PageMember::factory()->setUser($user3)->setOwner($page)->create();

        $params = [
            'q'     => $fullName,
            'view'  => 'recent',
            'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];

        $checkCount = 1;

        $result = $this->repository->viewPageMembers($user, $page->entityId(), $params);
        $this->assertTrue($checkCount == count($result->items()));
    }
}
