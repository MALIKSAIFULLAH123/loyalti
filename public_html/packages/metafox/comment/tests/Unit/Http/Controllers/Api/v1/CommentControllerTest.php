<?php

namespace Unit\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Comment\Http\Controllers\Api\v1\CommentController;
use MetaFox\Comment\Http\Controllers\Api\v1\CommentController as Controller;
use MetaFox\Comment\Http\Requests\v1\Comment\HideRequest;
use MetaFox\Comment\Http\Requests\v1\Comment\IndexRequest;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Repositories\CommentHistoryRepositoryInterface;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\User\Models\User;
use Tests\TestCase;

/**
 * @property CommentController      $controller
 * @property \Mockery\MockInterface $repository
 * @property \Mockery\MockInterface $historyRepository
 */
class CommentControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockUser = $this->asAdminUser();

        $this->repository        = $this->partialMock(CommentRepositoryInterface::class);
        $this->historyRepository = $this->partialMock(CommentHistoryRepositoryInterface::class);

        // append here`
        $this->controller = $this->app->make(CommentController::class);
    }

    public function testActionDestroy()
    {
        $this->repository->shouldReceive('deleteCommentById')
            ->with($this->mockUser, 1)
            ->once()
            ->andReturn([]);

        /** @var JsonResponse $response */
        $response = app()->call([$this->controller, 'destroy'], ['id' => 1]);

        $this->assertTrue($response->isOk());
    }

    public function provideActionHide()
    {
        return [
            [
                [
                    'comment_id' => 1, 'is_hidden' => false,
                ], true,
                [
                    'comment_id' => 1, 'is_hidden' => false,
                ], true,
                [
                    'comment_id' => 1, 'is_hidden' => true,
                ], true,
                [
                    'comment_id' => 1, 'is_hidden' => false,
                ], false,
            ],
        ];
    }

    /**
     * @param $params
     * @param $success
     * @return void
     * @dataProvider provideActionHide
     */
    public function testActionHide($params, $success)
    {
        $user = new User(['id' => 1]);
        $this->be($user);
        $comment = Comment::factory()->create();

        $this->partialMock(HideRequest::class)
            ->shouldReceive('validated')
            ->once()
            ->with()
            ->andReturn($params);

        $this->repository->shouldReceive([
            'hideCommentGlobal' => $success,
            'hideComment'       => $success,
        ])
            ->withAnyArgs()
            ->andReturn($success);

        $this->repository->shouldReceive('viewComment')
            ->with($user, $params['comment_id'])
            ->once()
            ->andReturn($comment);

        /** @var JsonResponse $response */
        $response = $this->app->call([$this->controller, 'hide']);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testActionIndex()
    {
        $params = [];

        $this->partialMock(IndexRequest::class)
            ->shouldReceive('validated')
            ->once()
            ->withNoArgs()
            ->andReturn($params);

        $this->repository->shouldReceive('viewComments')
            ->with($this->mockUser, $params)
            ->once()
            ->andReturn(new \Illuminate\Support\Collection());

        /** @var JsonResponse $response */
        $response = $this->app->call([$this->controller, 'index'], ['id' => 1]);

        $this->assertInstanceOf(JsonResource::class, $response);
    }

    public function testActionShow()
    {
        $user    = $this->mockUser;
        $comment = Comment::factory()->setUser($user)->makeOne();

        $this->repository->shouldReceive('viewComment')
            ->withAnyArgs()
            ->once()
            ->andReturn($comment);

        /** @var JsonResponse $response */
        $response = $this->app->call([$this->controller, 'show'], ['id' => 1]);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
