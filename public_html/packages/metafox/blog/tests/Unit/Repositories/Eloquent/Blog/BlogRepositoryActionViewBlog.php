<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent\Blog;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class BlogRepositoryActionViewBlog extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(BlogRepositoryInterface::class);
        $this->assertInstanceOf(BlogRepository::class, $repository);
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0]);
        $this->assertNotEmpty($item);

        return [$item, $repository];
    }

    /**
     * @depends testInstance
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testViewBlog(array $data): array
    {
        /**
         * @var BlogRepositoryInterface $repository
         * @var Model                   $item
         */
        [$item, $repository] = $data;
        $user                = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $result = $repository->viewBlog($user, $item->id);

        $this->assertTrue(($item->id == $result->id));

        return [$user, $item, $repository];
    }

    /**
     * @depends testViewBlog
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testViewNoHasPermission(array $data)
    {
        /**
         * @var User                    $user
         * @var Model                   $item
         * @var BlogRepositoryInterface $repository
         */
        [$user, $item, $repository] = $data;

        $item->update(['is_draft' => 1]);

        $this->expectException(AuthorizationException::class);
        $repository->viewBlog($user, $item->id);
    }

    /**
     * @depends testViewBlog
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testViewNotFound(array $data)
    {
        /**
         * @var User                    $user
         * @var Model                   $item
         * @var BlogRepositoryInterface $repository
         */
        [$user, , $repository] = $data;

        $this->expectException(ModelNotFoundException::class);
        $repository->viewBlog($user, 0);
    }
}
