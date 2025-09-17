<?php

namespace MetaFox\Like\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Like\Http\Requests\v1\Like\DeleteRequest;
use MetaFox\Like\Http\Requests\v1\Like\IndexRequest;
use MetaFox\Like\Http\Requests\v1\Like\LikeTabsRequest;
use MetaFox\Like\Http\Requests\v1\Like\StoreRequest;
use MetaFox\Like\Http\Resources\v1\Like\LikeItemCollection as ItemCollection;
use MetaFox\Like\Repositories\LikeRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class LikeController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group like
 */
class LikeController extends ApiController
{
    /**
     * @var LikeRepositoryInterface
     */
    private LikeRepositoryInterface $repository;

    /**
     * @param LikeRepositoryInterface $repository
     */
    public function __construct(LikeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->viewLikes(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params     = $request->validated();
        $itemId     = $params['item_id'];
        $itemType   = $params['item_type'];
        $reactionId = $params['reaction_id'];

        $result = $this->repository->createLike(user(), $itemId, $itemType, $reactionId);

        return $this->success($result);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteLikeById(user(), $id);

        return $this->success();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function deleteByUserAndItem(DeleteRequest $request): JsonResponse
    {
        $params   = $request->validated();
        $itemId   = $params['item_id'];
        $itemType = $params['item_type'];
        $data     = $this->repository->deleteByUserAndItem(user(), $itemId, $itemType);

        return $this->success($data);
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function viewLikeTabs(LikeTabsRequest $request): JsonResponse
    {
        $params   = $request->validated();
        $itemId   = $params['item_id'];
        $itemType = $params['item_type'];
        $results  = $this->repository->viewLikeTabs(user(), $itemId, $itemType);

        return $this->success($results);
    }
}
