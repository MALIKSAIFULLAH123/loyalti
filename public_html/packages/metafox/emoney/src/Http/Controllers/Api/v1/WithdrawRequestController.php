<?php

namespace MetaFox\EMoney\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Policies\WithdrawRequestPolicy;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\EMoney\Http\Resources\v1\WithdrawRequest\WithdrawRequestItemCollection as ItemCollection;
use MetaFox\EMoney\Http\Resources\v1\WithdrawRequest\WithdrawRequestDetail as Detail;
use MetaFox\EMoney\Repositories\WithdrawRequestRepositoryInterface;
use MetaFox\EMoney\Http\Requests\v1\WithdrawRequest\IndexRequest;
use MetaFox\EMoney\Http\Requests\v1\WithdrawRequest\StoreRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\EMoney\Http\Controllers\Api\WithdrawRequestController::$controllers;
 */

/**
 * Class WithdrawRequestController.
 * @codeCoverageIgnore
 * @ignore
 */
class WithdrawRequestController extends ApiController
{
    /**
     * @var WithdrawRequestRepositoryInterface
     */
    private WithdrawRequestRepositoryInterface $repository;

    /**
     * WithdrawRequestController Constructor.
     *
     * @param WithdrawRequestRepositoryInterface $repository
     */
    public function __construct(WithdrawRequestRepositoryInterface $repository)
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

        $data = $this->repository->viewRequests($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();

        $params = $request->validated();

        $amount = Arr::get($params, 'amount');

        $currency = Arr::get($params, 'currency');

        $service = Arr::get($params, 'withdraw_service');

        policy_authorize(WithdrawRequestPolicy::class, 'create', $context, $currency, $amount);

        $data   = $this->repository->createRequest($context, $currency, $amount, $service);

        return $this->success(new Detail($data), [], __p('ewallet::phrase.request_was_created_successfully'));
    }

    public function cancel(int $id): JsonResponse
    {
        $context = user();

        $request = $this->repository->find($id);

        policy_authorize(WithdrawRequestPolicy::class, 'cancel', $context, $request);

        $this->repository->cancelRequest($context, $request);

        $request->refresh();

        return $this->success(new Detail($request), [], __p('ewallet::phrase.request_was_cancelled_successfully'));
    }

    public function show(int $id): JsonResource
    {
        $request = $this->repository->find($id);

        $context = user();

        policy_authorize(WithdrawRequestPolicy::class, 'view', $context, $request);

        return new Detail($request);
    }
}
