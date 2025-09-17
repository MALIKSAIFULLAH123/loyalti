<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Http\Requests\v1\PointTransaction\Admin\IndexRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction\Admin\PointTransactionItemCollection as ItemCollection;
use MetaFox\ActivityPoint\Repositories\PointTransactionRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\ActivityPoint\Http\Controllers\Api\PointTransactionAdminController::$controllers;.
 */

/**
 * Class PointTransactionAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class PointTransactionAdminController extends ApiController
{
    /**
     * @var PointTransactionRepositoryInterface
     */
    private PointTransactionRepositoryInterface $repository;

    /**
     * PointTransactionAdminController Constructor.
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
     * @param  IndexRequest            $request
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResource
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->viewTransactionsAdmin($context, $params);

        return new ItemCollection($data);
    }
}
