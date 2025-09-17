<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Policies\BlogPolicy;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository as Repository;
use Tests\TestCases\TestRepository;

/**
 * @property \Mockery\MockInterface|Repository $repository;
 * @group repositories
 */
class BlogRepositoryTest extends TestRepository
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createRepositoryPartialMock(Repository::class);
    }

    public static function provide_viewMode()
    {
        $matrix = static::mixCriteria([
            'view' => ['all', 'my', 'friend', 'pending', 'feature', 'sponsor', 'my_pending', 'search'],
            'sort' => ['created_at'],
        ]);

        return $matrix;
    }

    /**
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @dataProvider provide_viewMode
     * @testdox View blog view=$view, sort=$sort, q=$q
     */
    public function testViewBlogs($view, $sort, $q = null)
    {
        $user = $this->createMockUser(1);
        $owner = $user;

        $this->actingAs($user);

        $attributes = [
            'q'           => '',
            'user_id'     => 1,
            'view'        => $view,
            'limit'       => 10,
            'sort'        => $sort,
            'sort_type'   => 'desc',
            'category_id' => 0,
        ];

        $response = $this->repository->viewBlogs($user, $owner, $attributes);

        $this->assertInstanceOf(Paginator::class, $response);
    }

    public function testCreateFailureBecauseOfPermission()
    {
        // user post a blog.
        $context = $this->createMockUser();

        $this->partialMock(BlogPolicy::class)
            ->shouldReceive('create')
            ->with($context, $context)
            ->andReturn(false);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $this->repository->createBlog($context, $context, []);
    }

    public static function provide_testMethodCreateBlog()
    {
        yield 'User post blog' => [
            fn() => [
                'userId'      => 1,
                'ownerId'     => 1,
                'title'       => 'blog title 01',
                'privacy'     => 0,
                'temp_file'   => null,
                'attachments' => null,
            ], function (Blog $blog) {
                static::assertSame(1, $blog->userId());
                static::assertSame(1, $blog->ownerId());
                static::assertSame('blog title 01', $blog->title);
                static::assertSame(0, $blog->privacy);
            },
        ];

        yield 'User post blog with attachments' => [
            fn() => [
                'userId'      => 1,
                'ownerId'     => 1,
                'title'       => 'blog title 02',
                'privacy'     => 1,
                'temp_file'   => null,
                'attachments' => [1, 2, 3],
            ],
            function (Blog $blog) {
                static::assertSame('blog title 02', $blog->title);
                static::assertEquals(null, $blog->image_file_id);
                static::assertEquals(1, $blog->privacy);
            },
        ];

        yield 'User post blog on others profile' => [
            fn() => [
                'userId'      => 1,
                'ownerId'     => 2,
                'title'       => uniqid('blog title '),
                'privacy'     => 2,
                'temp_file'   => null,
                'attachments' => [],
            ], null,
        ];

        yield 'User post blog with photo' => [
            fn() => [
                'userId'      => 1,
                'ownerId'     => 1,
                'title'       => uniqid('blog title '),
                'privacy'     => 2,
                'temp_file'   => 2,
                'attachments' => null,
            ], null,
        ];

        yield 'User post blog to others profile with photo' => [
            fn() => [
                'userId'      => 1,
                'ownerId'     => 2,
                'title'       => uniqid('blog title '),
                'privacy'     => 2,
                'temp_file'   => 2,
                'attachments' => null,
            ], null,
        ];
    }

    /**
     * @see          \MetaFox\Blog\Repositories\Eloquent\BlogRepository::createBlog
     * @dataProvider provide_testMethodCreateBlog
     */
    public function testCreateBlog($fn, $assertion)
    {
        $data = is_callable($fn) ? $fn() : $fn;
        $guards = ['userId', 'ownerId'];
        extract(Arr::only($data, $guards));
        $attributes = Arr::except($data, $guards);

        // disable event, test logic that defined on repository only.
        Event::fake();

        // user post a blog.
        $context = $this->createMockUser($userId);
        $owner = $this->createMockUser($ownerId);

        $this->be($context);

        $this->mock(BlogPolicy::class)->allows(['create' => true, 'view' => true, 'autoApprove'=>true]);

        $this->mock(\MetaFox\Platform\Contracts\UploadFile::class)
            ->shouldReceive('getFileId')
            ->with($attributes['temp_file'], true)
            ->atMost()
            ->andReturn($attributes['temp_file']);

        $this->mock(\MetaFox\Core\Repositories\AttachmentRepositoryInterface::class)
            ->shouldReceive('updateItemId')
            ->withAnyArgs()
            ->once();

        $this->repository->shouldReceive('checkCanDelete')->once()->andReturn(true);

        $blog = $this->repository->createBlog($context, $owner, $attributes);

        $this->assertInstanceOf(Blog::class, $blog);

        if ($assertion) {
            $assertion($blog);
        }

        $blog->delete();
    }

    public static function provide_testUpdateBlog()
    {
        yield 'update blog title' => [
            [
                'title'        => 'sample blog',
                'userId'       => 1,
                'ownerId'      => 1,
                'temp_file'    => 0,
                'remove_image' => 0,
            ],
            function (Blog $blog) {
                static::assertEquals('sample blog', $blog->title);
            },
        ];

        yield 'Remove blog image' => [
            [
                'userId'       => 1,
                'ownerId'      => 1,
                'temp_file'    => 0,
                'remove_image' => 1,
            ], function (Blog $blog) {
                static::assertSame(null, $blog->image_file_id);
            },
        ];

        yield 'Update blog image' => [
            [
                'userId'       => 1,
                'ownerId'      => 1,
                'temp_file'    => 5,
                'remove_image' => 0,
            ], function (Blog $blog) {
                static::assertSame(5, $blog->image_file_id);
            },
        ];
    }

    public function testCreateBlogInstance()
    {
        $this->expectNotToPerformAssertions();

        return Blog::factory()->create();
    }

    /**
     * @see          \MetaFox\Blog\Repositories\Eloquent\BlogRepository::createBlog
     * @dataProvider provide_testUpdateBlog
     * @depends      testCreateBlogInstance
     */
    public function testUpdateBlog($fn, $assertion, $blog)
    {
        $guards = ['userId', 'ownerId'];
        $provide = is_callable($fn) ? $fn() : $fn;

        extract(Arr::only($provide, $guards));

        $attributes = Arr::except($provide, $guards);

        $id = 1;

        // disable event, test logic that defined on repository only.
        Event::fake();

        $this->repository->shouldReceive('find')
            ->with($id)
            ->once()
            ->andReturn($blog);

        // user post a blog.
        $context = $this->createMockUser($userId);

        $this->be($context);

        $this->mock(BlogPolicy::class)->allows(['update' => true, 'view' => true]);

        $this->mock(\MetaFox\Platform\Contracts\UploadFile::class)
            ->shouldReceive('getFileId')
            ->with($attributes['temp_file'], true)
            ->atMost()
            ->andReturn($attributes['temp_file']);

        $this->mock(\MetaFox\Core\Repositories\AttachmentRepositoryInterface::class)
            ->shouldReceive('updateItemId')
            ->withAnyArgs()
            ->once();

        $blog = $this->repository->updateBlog($context, $id, $attributes);

        if ($assertion) {
            $assertion($blog);
        }
    }

    public function testDeleteBlogById()
    {
        $id = 1;
        $user = $this->createMockUser();

        $this->actingAs($user);

        // pass privacy
        $this->mock(BlogPolicy::class)
            ->shouldReceive('delete')
            ->andReturn(true);

        $this->repository->shouldReceive('delete')
            ->with($id)
            ->once()
            ->andReturn(1);

        $this->repository->shouldReceive('find')
            ->with($id)
            ->once()
            ->andReturn(new Blog());

        $this->repository->deleteBlog($user, $id);
    }
}
