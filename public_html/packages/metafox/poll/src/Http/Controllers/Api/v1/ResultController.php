<?php

namespace MetaFox\Poll\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Poll\Http\Requests\v1\Result\IndexRequest;
use MetaFox\Poll\Http\Requests\v1\Result\StoreRequest;
use MetaFox\Poll\Http\Requests\v1\Result\UpdateRequest;
use MetaFox\Poll\Http\Resources\v1\Poll\PollDetail as Detail;
use MetaFox\Poll\Http\Resources\v1\Result\ResultItemCollection as ItemCollection;
use MetaFox\Poll\Repositories\ResultRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Poll\Http\Controllers\Api\ResultController::$controllers;
 */

/**
 * Class ResultController.
 */
class ResultController extends ApiController
{
    /**
     * @var ResultRepositoryInterface
     */
    public ResultRepositoryInterface $repository;

    public function __construct(ResultRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  IndexRequest  $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data = $this->repository->viewResults(user(), $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data = $this->repository->createResult(user(), $params);

        return $this->success(new Detail($data), [], __p('poll::phrase.your_vote_has_been_cast_successfully'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  int            $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data = $this->repository->updateResult(user(), $id, $params);

        return $this->success(new Detail($data), [], __p('poll::phrase.your_vote_has_been_cast_successfully'));
    }
}
