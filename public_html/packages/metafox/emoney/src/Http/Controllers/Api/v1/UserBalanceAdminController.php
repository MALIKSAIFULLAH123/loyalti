<?php

namespace MetaFox\EMoney\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Http\Requests\v1\UserBalance\Admin\AdjustmentHistoryRequest;
use MetaFox\EMoney\Http\Requests\v1\UserBalance\Admin\IndexRequest;
use MetaFox\EMoney\Http\Requests\v1\UserBalance\Admin\ReduceRequest;
use MetaFox\EMoney\Http\Requests\v1\UserBalance\Admin\SendRequest;
use MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin\UserBalanceItem;
use MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin\UserBalanceItemCollection;
use MetaFox\EMoney\Policies\UserBalancePolicy;
use MetaFox\EMoney\Services\Contracts\UserBalanceServiceInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin\AdjustmentHistoryItemCollection;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\EMoney\Http\Controllers\Api\UserBalanceAdminController::$controllers;
 */

/**
 * Class UserBalanceAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class UserBalanceAdminController extends ApiController
{
    public function __construct(protected UserBalanceServiceInterface $service)
    {
    }

    public function index(IndexRequest $request): ResourceCollection
    {
        $params = $request->validated();

        $users = $this->service->manageUserBalances($params);

        return resolve(UserBalanceItemCollection::class, [
            'resource' => $users,
        ]);
    }

    public function send(SendRequest $request): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $user = resolve(UserRepositoryInterface::class)->find(Arr::pull($data, 'user_id'));

        policy_authorize(UserBalancePolicy::class, 'send', $context, $user);

        $this->service->sendAmountToSpecificUserBalanceByCurrency($context, $user, Arr::pull($data, 'currency'), Arr::pull($data, 'price'));

        return $this->success(resolve(UserBalanceItem::class, [
            'resource' => $user,
        ]), [], __p('ewallet::admin.sent_successfully'));
    }

    public function reduce(ReduceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $user = resolve(UserRepositoryInterface::class)->find(Arr::pull($data, 'user_id'));

        policy_authorize(UserBalancePolicy::class, 'reduce', $context, $user);

        $this->service->reduceAmountToSpecificUserBalanceByCurrency($context, $user, Arr::pull($data, 'currency'), Arr::pull($data, 'price'));

        return $this->success(resolve(UserBalanceItem::class, [
            'resource' => $user,
        ]), [], __p('ewallet::admin.reduced_successfully'));
    }

    public function viewAdjustmentHistories(AdjustmentHistoryRequest $request, int $id): ResourceCollection
    {
        $context = user();

        $user = resolve(UserRepositoryInterface::class)->find($id);

        $params = $request->validated();

        $histories = $this->service->manageAdjustmentHistories($context, $user, $params);

        return new AdjustmentHistoryItemCollection($histories);
    }
}
