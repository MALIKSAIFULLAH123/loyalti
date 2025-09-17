<?php

namespace MetaFox\Payment\Http\Controllers\Api\v1;

use MetaFox\Payment\Http\Requests\v1\Order\IndexRequest;
use MetaFox\Payment\Http\Requests\v1\Order\UpdateRequest;
use MetaFox\Payment\Http\Resources\v1\Order\OrderDetail as Detail;
use MetaFox\Payment\Http\Resources\v1\Order\OrderItemCollection as ItemCollection;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Payment\Http\Controllers\Api\OrderController::$controllers.
 */

/**
 * Class OrderController.
 * @codeCoverageIgnore
 * @ignore
 */
class OrderController extends ApiController
{
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $repository;

    /**
     * OrderController Constructor.
     *
     * @param OrderRepositoryInterface $repository
     */
    public function __construct(OrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest          $request
     * @return ItemCollection<Order>
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->paginate($params['limit'] ?? 100);

        return new ItemCollection($data);
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show($id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return Detail
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): Detail
    {
        $params = $request->validated();
        $order  = $this->repository->find($id);

        Payment::placeOrder($order, $params['gateway_id']);

        return new Detail($order);
    }
}
