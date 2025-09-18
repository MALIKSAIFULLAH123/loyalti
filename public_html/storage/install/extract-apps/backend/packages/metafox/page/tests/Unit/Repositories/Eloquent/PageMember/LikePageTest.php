<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\PageMember;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Repositories\Eloquent\PageMemberRepository;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;

class LikePageTest extends TestCase
{
    public function testCreateInstance(): PageMemberRepositoryInterface
    {
        $service = resolve(PageMemberRepositoryInterface::class);
        $this->assertInstanceOf(PageMemberRepository::class, $service);

        return $service;
    }

    /**
     * @depends testCreateInstance
     * @param PageMemberRepositoryInterface $service
     *
     * @throws ValidatorException
     * @throws AuthorizationException
     */
    public function testSuccess($service)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);
        $category = Category::factory()->create();

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $this->actingAs($user2);
        $service->likePage($user2, $page->entityId());

        $memberExist = $service->getModel()->newQuery()
            ->where('page_id', $page->entityId())
            ->where('user_id', $user2->entityId())
            ->exists();

        $this->assertTrue($memberExist);
    }
}
