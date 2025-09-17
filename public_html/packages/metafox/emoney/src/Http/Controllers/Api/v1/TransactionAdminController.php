<?php

namespace MetaFox\EMoney\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\EMoney\Http\Resources\v1\Transaction\Admin\TransactionItemCollection as ItemCollection;
use MetaFox\EMoney\Http\Resources\v1\Transaction\Admin\TransactionDetail as Detail;
use MetaFox\EMoney\Repositories\TransactionRepositoryInterface;
use MetaFox\EMoney\Http\Requests\v1\Transaction\Admin\IndexRequest;
use MetaFox\EMoney\Http\Requests\v1\Transaction\Admin\StoreRequest;
use MetaFox\EMoney\Http\Requests\v1\Transaction\Admin\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\EMoney\Http\Controllers\Api\TransactionAdminController::$controllers;
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
}
