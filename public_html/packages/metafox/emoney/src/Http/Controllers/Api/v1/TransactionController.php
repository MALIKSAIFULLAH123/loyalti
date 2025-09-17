<?php

namespace MetaFox\EMoney\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\EMoney\Policies\TransactionPolicy;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\EMoney\Http\Resources\v1\Transaction\TransactionItemCollection as ItemCollection;
use MetaFox\EMoney\Repositories\TransactionRepositoryInterface;
use MetaFox\EMoney\Http\Requests\v1\Transaction\IndexRequest;
use MetaFox\EMoney\Http\Resources\v1\Transaction\TransactionItem;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\EMoney\Http\Controllers\Api\TransactionController::$controllers;
 */

/**
 * Class TransactionController.
 * @codeCoverageIgnore
 * @ignore
 */
class TransactionController extends ApiController
{
    /**
     * @var TransactionRepositoryInterface
     */
    private TransactionRepositoryInterface $repository;

    /**
     * TransactionController Constructor.
     *
     * @param TransactionRepositoryInterface $repository
     */
    public function __construct(TransactionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = user();

        if ($context->isGuest()) {
            throw new AuthorizationException();
        }

        $data = $this->repository->viewTransactions($context, $params);

        return new ItemCollection($data);
    }

    /**
     * @param  int                                      $id
     * @return JsonResource
     * @throws AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function show(int $id): JsonResource
    {
        $transaction = $this->repository->find($id);

        $context = user();

        policy_authorize(TransactionPolicy::class, 'view', $context, $transaction);

        return new TransactionItem($transaction);
    }
}
