<?php

namespace MetaFox\Payment\Http\Controllers\Api\v1;

use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Payment\Http\Resources\v1\Order\Admin\OrderItemCollection as ItemCollection;
use MetaFox\Payment\Repositories\OrderAdminRepositoryInterface;
use MetaFox\Payment\Http\Requests\v1\Order\Admin\IndexRequest;
/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Payment\Http\Controllers\Api\OrderAdminController::$controllers;
 */

/**
 * Class OrderAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class OrderAdminController extends ApiController
{
    /**
     * @var OrderAdminRepositoryInterface
     */
    private OrderAdminRepositoryInterface $repository;

    /**
     * OrderAdminController Constructor
     *
     * @param  OrderAdminRepositoryInterface $repository
     */
    public function __construct(OrderAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->viewOrders($params);

        return new ItemCollection($data);
    }
}
