<?php

namespace MetaFox\Featured\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Featured\Http\Resources\v1\Transaction\Admin\TransactionItemCollection as ItemCollection;
use MetaFox\Featured\Http\Resources\v1\Transaction\Admin\TransactionDetail as Detail;
use MetaFox\Featured\Repositories\TransactionRepositoryInterface;
use MetaFox\Featured\Http\Requests\v1\Transaction\Admin\IndexRequest;
use MetaFox\Featured\Http\Requests\v1\Transaction\Admin\StoreRequest;
use MetaFox\Featured\Http\Requests\v1\Transaction\Admin\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Featured\Http\Controllers\Api\TransactionAdminController::$controllers;
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

        $data = $this->repository->viewTransactions($params);

        return new ItemCollection($data);
    }
}
