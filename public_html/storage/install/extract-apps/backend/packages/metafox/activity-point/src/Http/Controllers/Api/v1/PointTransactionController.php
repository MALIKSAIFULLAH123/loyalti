<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\ActivityPoint\Http\Requests\v1\PointTransaction\IndexRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction\PointTransactionDetail as Detail;
use MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction\PointTransactionItemCollection as ItemCollection;
use MetaFox\ActivityPoint\Repositories\PointTransactionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\ActivityPoint\Http\Controllers\Api\PointTransactionController::$controllers;
 */

/**
 * Class PointTransactionController.
 * @codeCoverageIgnore
 * @ignore
 * @authenticated
 * @group activitypoint
 */
class PointTransactionController extends ApiController
{
    /**
     * @var PointTransactionRepositoryInterface
     */
    private PointTransactionRepositoryInterface $repository;

    /**
     * PointTransactionController Constructor.
     *
     * @param PointTransactionRepositoryInterface $repository
     */
    public function __construct(PointTransactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResource
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->viewTransactions($context, $params);

        return new ItemCollection($data);
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function show(int $id): JsonResponse
    {
        $context = user();
        $data    = $this->repository->viewTransaction($context, $id);

        return $this->success(new Detail($data));
    }
}
