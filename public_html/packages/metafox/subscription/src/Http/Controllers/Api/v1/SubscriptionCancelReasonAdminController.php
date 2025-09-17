<?php

namespace MetaFox\Subscription\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\Admin\ActiveRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\Admin\DeleteRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\Admin\IndexRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\Admin\StoreRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionCancelReason\Admin\UpdateRequest;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin\CreateSubscriptionCancelReasonForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin\DeleteSubscriptionCancelReasonForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin\EditSubscriptionCancelReasonForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin\SubscriptionCancelReasonDetail as Detail;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin\SubscriptionCancelReasonItemCollection as ItemCollection;
use MetaFox\Subscription\Repositories\SubscriptionCancelReasonRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Subscription\Http\Controllers\Api\SubscriptionCancelReasonAdminController::$controllers;
 */

/**
 * Class SubscriptionCancelReasonAdminController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class SubscriptionCancelReasonAdminController extends ApiController
{
    /**
     * @var SubscriptionCancelReasonRepositoryInterface
     */
    private SubscriptionCancelReasonRepositoryInterface $repository;

    /**
     * SubscriptionCancelReasonAdminController Constructor.
     *
     * @param SubscriptionCancelReasonRepositoryInterface $repository
     */
    public function __construct(SubscriptionCancelReasonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->viewReasons($context, $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->createReason($context, $params);

        $nextAction = [
            'type'    => 'navigate',
            'payload' => [
                'url'     => '/subscription/cancel-reason/browse',
                'replace' => true,
            ],

        ];
        Artisan::call('cache:reset');

        return $this->success(
            new Detail($data),
            [
                'nextAction' => $nextAction,
            ],
            __p('subscription::admin.reason_successfully_created')
        );
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
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->updateReason($context, $id, $params);

        Artisan::call('cache:reset');
        return $this->success(new Detail($data), [], __p('subscription::admin.reason_successfully_updated'));
    }

    /**
     * @param DeleteRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function destroy(DeleteRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $this->repository->deleteReason($context, $id, $data);

        return $this->success([
            'id' => $id,
        ], [], __p('subscription::admin.reason_successfully_deleted'));
    }

    /**
     * Get creation form.
     *
     * @return CreateSubscriptionCancelReasonForm
     */
    public function create(): CreateSubscriptionCancelReasonForm
    {
        return new CreateSubscriptionCancelReasonForm();
    }

    /**
     * Get updating form.
     *
     * @param int $id
     *
     * @return EditSubscriptionCancelReasonForm
     */
    public function edit(int $id): EditSubscriptionCancelReasonForm
    {
        $resource = $this->repository->find($id);

        return new EditSubscriptionCancelReasonForm($resource);
    }

    /**
     * @param ActiveRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $context = user();

        $data = $request->validated();

        $isActive = Arr::get($data, 'active', false);

        $this->repository->activeReason($context, $id, $isActive);

        return $this->success([
            'id'        => $id,
            'is_active' => $isActive,
        ], [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * @param int $id
     *
     * @return DeleteSubscriptionCancelReasonForm
     */
    public function delete(int $id): DeleteSubscriptionCancelReasonForm
    {
        $reason = $this->repository->find($id);

        return new DeleteSubscriptionCancelReasonForm($reason);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids');

        $context = user();

        $this->repository->order($context, $orderIds);

        return $this->success([], [], __p('subscription::admin.reasons_successfully_ordered'));
    }
}
