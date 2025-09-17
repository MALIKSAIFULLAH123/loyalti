<?php

namespace MetaFox\Blog\Tests\Unit\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Paginator;
use MetaFox\Blog\Http\Controllers\Api\v1\BlogController as ApiController;
use MetaFox\Blog\Http\Resources\v1\Blog\BlogDetail;
use MetaFox\Blog\Http\Resources\v1\Blog\SearchBlogForm as SearchForm;
use MetaFox\Blog\Http\Resources\v1\Blog\StoreBlogForm;
use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Policies\BlogPolicy;
use Tests\TestCases\TestController;

/**
 * @property \Mockery\MockInterface|ApiController $controller;
 * @property \Mockery\MockInterface               $repository
 * @group controllers
 */
class BlogControllerTest extends TestController
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->partialMock(\MetaFox\Blog\Repositories\BlogRepositoryInterface::class);
        $this->controller = $this->app->make(ApiController::class);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::index
     */
    public function testActionIndex()
    {
        $user = $this->asAdminUser();
        $data = ['user_id' => 0];

        $this->mock(\MetaFox\Blog\Http\Requests\v1\Blog\IndexRequest::class)
            ->shouldReceive('validated')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $this->repository->shouldReceive('viewBlogs')
            ->with($user, $user, $data)
            ->once()
            ->andReturn(new Paginator([], 10));

        $response = $this->app->call([$this->controller, 'index'], []);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::store
     */
    public function testActionStore()
    {
        $user  = $this->createMockUser();
        $owner = $user;
        $this->actingAs($user);
        $blog = $this->partialMock(Blog::class);

        $blog->shouldReceive('getOwnerPendingMessage')
            ->once()
            ->andReturn(null);

        $this->mock('alias:' . BlogDetail::class);

        $blog->shouldReceive('isApproved')
            ->once()
            ->andReturn(true);

        $data = ['owner_id' => 0, 'is_draft' => false];
        $this->mock(\MetaFox\Blog\Http\Requests\v1\Blog\StoreRequest::class)
            ->shouldReceive('validated')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $this->repository->shouldReceive('createBlog')
            ->with($user, $owner, $data)
            ->once()
            ->andReturn($blog);

        $this->mock(\MetaFox\FloodControl\Facades\FloodControl::class)
            ->shouldReceive('checkFloodControlWhenCreateItem')
            ->with($user, Blog::ENTITY_TYPE)
            ->once()
            ->andReturn(true);

        $this->mock(\MetaFox\QuotaControl\Facades\QuotaControl::class)
            ->shouldReceive('checkQuotaControlWhenCreateItem')
            ->with($user, Blog::ENTITY_TYPE)
            ->once()
            ->andReturn(true);

        $response = $this->app->call([$this->controller, 'store'], []);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::show
     */
    public function testActionShow()
    {
        $id   = 1;
        $user = $this->createMockUser();
        $this->actingAs($user);

        $this->repository->shouldReceive('viewBlog')
            ->with($user, $id)
            ->andReturn(new Blog());

        $response = $this->app->call([$this->controller, 'show'], [
            'id' => $id,
        ]);

        $this->assertInstanceOf(BlogDetail::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::update
     */
    public function testActionUpdate()
    {
        $id = 1;
        $user = $this->createNormalUser();
        $this->actingAs($user);
        $data = ['published' => true];

        $this->mock(\MetaFox\Blog\Http\Requests\v1\Blog\UpdateRequest::class)
            ->shouldReceive('validated')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $this->repository->shouldReceive('updateBlog')
            ->with($user, $id, $data)
            ->once()
            ->andReturn(new Blog());

        $response = $this->app->call([$this->controller, 'update'], [
            'id' => $id,
        ]);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::destroy
     */
    public function testActionDestroy()
    {
        $id = 1;

        $user = $this->createMockUser();
        $this->actingAs($user);

        $this->repository->shouldReceive('deleteBlog')
            ->with($user, $id)
            ->once();

        $response = $this->app->call([$this->controller, 'destroy'], [
            'id' => $id,
        ]);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::patch
     */
    public function testActionPatch()
    {
        $id = 1;
        $data = [];
        $user = $this->createMockUser();
        $this->actingAs($user);

        $this->mock(\MetaFox\Blog\Http\Requests\v1\Blog\PatchRequest::class)
            ->shouldReceive('validated')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $this->repository->shouldReceive('find')
            ->with($id)
            ->andReturn(new Blog());

        $response = $this->app->call([$this->controller, 'patch'], [
            'id' => $id,
        ]);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::sponsor
     */
    public function testActionSponsor()
    {
        $id   = 1;
        $data = ['sponsor' => 1];
        $user = $this->createMockUser();
        $this->actingAs($user);
        $blog = new Blog(['id'=>$id]);

        $this->mock(\MetaFox\Platform\Http\Requests\v1\SponsorRequest::class)
            ->shouldReceive('validated')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $this->repository->shouldReceive('sponsor')
            ->with($user, $id, true)
            ->once();

        $this->repository->shouldReceive('find')
            ->with($id)
            ->andReturn($blog);

        $response = $this->app->call([$this->controller, 'sponsor'], [
            'id' => $id,
        ]);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::feature
     */
    public function testActionFeature()
    {
        $id   = 1;
        $data = ['feature' => 1];
        $user = $this->createMockUser();
        $this->actingAs($user);

        $this->mock(\MetaFox\Platform\Http\Requests\v1\FeatureRequest::class)
            ->shouldReceive('validated')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $this->repository->shouldReceive('feature')
            ->with($user, $id, true)
            ->once();

        $response = $this->app->call([$this->controller, 'feature'], [
            'id' => $id,
        ]);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::approve
     */
    public function testActionApprove()
    {
        $id   = 1;
        $user = $this->createMockUser();
        $this->actingAs($user);

        $this->repository->shouldReceive('approve')
            ->with($user, $id)
            ->once();

        $response = $this->app->call([$this->controller, 'approve'], [
            'id' => $id,
        ]);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::publish
     */
    public function testActionPublish()
    {
        $id   = 1;
        $user = $this->createMockUser();
        $this->actingAs($user);

        $this->repository->shouldReceive('publish')
            ->with($user, $id)
            ->once()
            ->andReturn(new Blog());

        $response = $this->app->call([$this->controller, 'publish'], [
            'id' => $id,
        ]);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::formStore
     */
    public function testActionFormStore()
    {
        $data = ['owner_id' => 1];
        $user = $this->createMockUser();
        $this->actingAs($user);

        $this->mock(BlogPolicy::class)
            ->shouldReceive('create')
            ->with($user)
            ->andReturn(true);

        $this->mock(\MetaFox\Blog\Http\Requests\v1\Blog\CreateFormRequest::class)
            ->shouldReceive('validated')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $response = $this->app->call([$this->controller, 'formStore'], []);

        $this->assertInstanceOf(StoreBlogForm::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::formUpdate
     */
    public function testActionFormUpdate()
    {
        $id   = 1;
        $user = $this->createMockUser();
        $this->actingAs($user);
        $blog = new Blog();

        $this->mock(BlogPolicy::class)
            ->shouldReceive('update')
            ->with($user, $blog)
            ->andReturn(true);

        $this->repository->shouldReceive('find')
            ->with($id)
            ->andReturn($blog);

        $response = $this->app->call([$this->controller, 'formUpdate'], [
            'id' => $id,
        ]);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::sponsorInFeed
     */
    public function testActionSponsorInFeed()
    {
        $id   = 1;
        $data = ['sponsor' => 1];
        $user = $this->createMockUser();
        $this->actingAs($user);

        $this->mock(\MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest::class)
            ->shouldReceive('validated')
            ->withNoArgs()
            ->once()
            ->andReturn($data);

        $this->repository->shouldReceive('find')
            ->with($id)
            ->once()
            ->andReturn(new Blog());

        $this->repository->shouldReceive('sponsorInFeed')
            ->with($user, $id, true)
            ->once();

        $response = $this->app->call([$this->controller, 'sponsorInFeed'], [
            'id' => $id,
        ]);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::searchForm
     */
    public function testActionSearchForm()
    {
        $response = $this->app->call([$this->controller, 'searchForm'], []);

        $this->assertInstanceOf(SearchForm::class, $response);
    }
}
