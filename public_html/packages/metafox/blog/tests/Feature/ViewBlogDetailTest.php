<?php

namespace MetaFox\Blog\Tests\Feature;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ViewBlogDetailTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreateInstance(): array
    {
        $user    = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $service = resolve(BlogRepositoryInterface::class);
        $item    = Blog::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0]);

        $this->assertInstanceOf(HasTotalView::class, $item);
        $this->assertSame(0, $item->total_view);

        return [$user, $service, $item];
    }

    /**
     * @depends testCreateInstance
     * @param  array<int, mixed>             $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testViewDetailIncrementView(array $data): array
    {
        /** @var BlogRepositoryInterface $repository */
        /** @var HasTotalView $item */
        [$user, $repository, $item] = $data;

        $this->be($user);
        $blog = $repository->viewBlog($user, $item->entityId());
        $this->assertSame($item->total_view + 1, $blog->total_view);

        return $data;
    }
}
