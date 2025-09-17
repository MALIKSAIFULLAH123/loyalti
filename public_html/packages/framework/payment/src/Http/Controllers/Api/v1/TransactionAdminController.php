<?php

namespace MetaFox\Payment\Http\Controllers\Api\v1;

use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Payment\Http\Resources\v1\Transaction\Admin\TransactionItemCollection as ItemCollection;
use MetaFox\Payment\Http\Resources\v1\Transaction\Admin\TransactionDetail as Detail;
use MetaFox\Payment\Repositories\TransactionRepositoryInterface;
use MetaFox\Payment\Http\Requests\v1\Transaction\Admin\IndexRequest;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Payment\Http\Controllers\Api\TransactionAdminController::$controllers;
 */

/**
 * Class TransactionAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class TransactionAdminController extends ApiController
{
    /**
     * @var TransactionRepositoryInterface
     */
    private TransactionRepositoryInterface $repository;

    /**
     * TransactionAdminController Constructor
     *
     * @param  TransactionRepositoryInterface $repository
     */
    public function __construct(TransactionRepositoryInterface $repository)
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

        $data = $this->repository->viewTransactionsAdminCP($params);

        return new ItemCollection($data);
    }

    /**
     * View item
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
}
