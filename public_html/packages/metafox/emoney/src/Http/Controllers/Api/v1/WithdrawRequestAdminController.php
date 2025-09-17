<?php

namespace MetaFox\EMoney\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Http\Requests\v1\WithdrawRequest\Admin\DenyRequest;
use MetaFox\EMoney\Policies\WithdrawRequestPolicy;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\EMoney\Http\Resources\v1\WithdrawRequest\Admin\WithdrawRequestItemCollection as ItemCollection;
use MetaFox\EMoney\Http\Resources\v1\WithdrawRequest\Admin\WithdrawRequestDetail as Detail;
use MetaFox\EMoney\Repositories\WithdrawRequestRepositoryInterface;
use MetaFox\EMoney\Http\Requests\v1\WithdrawRequest\Admin\IndexRequest;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\EMoney\Http\Controllers\Api\WithdrawRequestAdminController::$controllers;
 */

/**
 * Class WithdrawRequestAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class WithdrawRequestAdminController extends ApiController
{
    /**
     * @var WithdrawRequestRepositoryInterface
     */
    private WithdrawRequestRepositoryInterface $repository;

    /**
     * WithdrawRequestAdminController Constructor.
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

        $data = $this->repository->manageRequests($context, $params);

        return new ItemCollection($data);
    }

    public function deny(DenyRequest $request): JsonResponse
    {
        $data = $request->validated();

        $item = $this->repository->find(Arr::get($data, 'id'));

        $reason = Arr::get($data, 'reason');

        $context = user();

        policy_authorize(WithdrawRequestPolicy::class, 'deny', $context, $item);

        $this->repository->denyRequest($context, $item, $reason);

        $item->refresh();

        return $this->success(new Detail($item), [], __p('ewallet::phrase.request_was_denied_successfully'));
    }

    public function approve(int $id): JsonResponse
    {
        $context = user();

        $request = $this->repository->find($id);

        policy_authorize(WithdrawRequestPolicy::class, 'approve', $context, $request);

        $data = $this->repository->approveRequest($context, $request);

        if (null === $data) {
            return $this->error(__p('ewallet::validation.this_request_is_not_available_for_approval'));
        }

        $status = Arr::get($data, 'withdraw_status', Support::WITHDRAW_STATUS_PROCESSING);

        return match ($status) {
            Support::WITHDRAW_STATUS_PROCESSING => $this->success([], [
                'nextAction' => [
                    'type'    => 'navigate',
                    'payload' => [
                        'url'    => Arr::get($data, 'gateway_redirect_url'),
                        'target' => '_blank',
                    ],
                ],
            ]),
            Support::WITHDRAW_STATUS_WAITING_CONFIRMATION => $this->success(new Detail($request->refresh()), [], __p('ewallet::admin.request_was_approved_successfully_please_waiting_confirmation_from_payee')),
            default                                       => $this->success(),
        };
    }

    public function payment(int $id): JsonResponse
    {
        $context = user();

        $request = $this->repository->find($id);

        policy_authorize(WithdrawRequestPolicy::class, 'payment', $context, $request);

        $data = $this->repository->paymentRequest($context, $request);

        if (null === $data) {
            return $this->error(__p('ewallet::validation.this_request_is_not_available_for_processing'));
        }

        return $this->success([], [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'    => Arr::get($data, 'gateway_redirect_url'),
                    'target' => '_blank',
                ],
            ],
        ]);
    }
}
