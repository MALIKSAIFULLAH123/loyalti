<?php

namespace MetaFox\Like\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Like\Http\Requests\v1\Reaction\IndexRequest;
use MetaFox\Like\Http\Resources\v1\Reaction\ReactionDetail as Detail;
use MetaFox\Like\Http\Resources\v1\Reaction\ReactionItemCollection as ItemCollection;
use MetaFox\Like\Repositories\ReactionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class ReactionController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group like
 */
class ReactionController extends ApiController
{
    /**
     * @var ReactionRepositoryInterface
     */
    private ReactionRepositoryInterface $repository;

    /**
     * @param ReactionRepositoryInterface $repository
     */
    public function __construct(ReactionRepositoryInterface $repository)
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
        $data   = $this->repository->viewReactionsForAdmin(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResource
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function viewReactionsForFE()
    {
        $data = $this->repository->viewReactionsForFE(user());

        return new ItemCollection($data);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthenticationException|AuthorizationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewReaction(user(), $id);

        return new Detail($data);
    }
}
