<?php

namespace MetaFox\Comment\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Comment\Http\Resources\v1\Pending\Admin\PendingDetail;
use MetaFox\Comment\Repositories\CommentAdminRepositoryInterface;
use MetaFox\Comment\Http\Requests\v1\Pending\Admin\IndexRequest;
use MetaFox\Comment\Http\Resources\v1\Pending\Admin\PendingItemCollection as ItemCollection;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class PendingAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 */
class PendingAdminController extends ApiController
{
    /**
     * @var CommentAdminRepositoryInterface
     */
    public CommentAdminRepositoryInterface $repository;

    /**
     * @param CommentAdminRepositoryInterface $repository
     */
    public function __construct(CommentAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse pending comment.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->viewPendingComment($params);

        return new ItemCollection($data);
    }

    /**
     * Approve pending comment.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function approve(int $id): JsonResponse
    {
        $pendingComment = $this->repository->approve(user(), $id);

        return $this->success(new PendingDetail($pendingComment), [], __p('comment::phrase.comment_was_approved_successfully'));
    }

    /**
     * Decline comment.
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->decline($id);

        return $this->success([], [], __p('comment::phrase.comment_was_declined_successfully'));
    }
}
