<?php

namespace MetaFox\Subscription\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionInvoice\Admin\IndexRequest;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin\ViewSubscriptionCancelReasonForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\Admin\SubscriptionInvoiceItem;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\Admin\SubscriptionInvoiceItemCollection;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\SubscriptionInvoiceDetail as Detail;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\SubscriptionInvoiceSimpleDetail as SimpleDetail;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoiceTransaction\SubscriptionInvoiceTransactionCollection;
use MetaFox\Subscription\Policies\SubscriptionInvoicePolicy;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Subscription\Http\Controllers\Api\SubscriptionInvoiceAdminController::$controllers.
 */

/**
 * Class SubscriptionInvoiceAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class SubscriptionInvoiceAdminController extends ApiController
{
    /**
     * @var SubscriptionInvoiceRepositoryInterface
     */
    private SubscriptionInvoiceRepositoryInterface $repository;

    /**
     * SubscriptionInvoiceAdminController Constructor.
     *
     * @param SubscriptionInvoiceRepositoryInterface $repository
     */
    public function __construct(SubscriptionInvoiceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ResourceCollection
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->viewInvoicesInAdminCP($context, $params);

        return new SubscriptionInvoiceItemCollection($data);
    }

    /**
     * @param  int          $id
     * @return JsonResponse
     */
    public function cancel(int $id): JsonResponse
    {
        $context = user();

        $result = $this->repository->updatePaymentForAdminCP($context, $id, Helper::getCanceledPaymentStatus());

        if (!$result) {
            return $this->error(__p('subscription::admin.can_not_cancel_this_subscription'));
        }

        $invoice = $this->repository->with(['package', 'user'])
            ->find($id);

        return $this->success(
            new SubscriptionInvoiceItem($invoice),
            [],
            __p('subscription::admin.subscription_successfully_cancelled')
        );
    }

    /**
     * @param  int          $id
     * @return JsonResponse
     */
    public function toggleActive(int $id): JsonResponse
    {
        $context = user();

        $result = $this->repository->updatePaymentForAdminCP($context, $id, Helper::getCompletedPaymentStatus());

        if (!$result) {
            return $this->error(__p('subscription::admin.can_not_activate_this_subscription'));
        }

        $invoice = $this->repository->with(['package', 'user'])
            ->find($id);

        return $this->success(
            new SubscriptionInvoiceItem($invoice),
            [],
            __p('subscription::admin.subscription_successfully_activated')
        );
    }

    /**
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function viewReason(int $id): JsonResponse
    {
        $context = user();

        $invoice = $this->repository->find($id);

        policy_authorize(SubscriptionInvoicePolicy::class, 'viewUserReasonAdminCP', $context, $invoice);

        $form = new ViewSubscriptionCancelReasonForm($invoice);

        return $this->success($form);
    }

    /**
     * @throws AuthenticationException
     */
    public function show(int $id): Detail
    {
        $context = user();

        $data = $this->repository->viewInvoice($context, $id);

        return new Detail($data);
    }

    public function viewTransactions(int $id): JsonResponse
    {
        $context = user();

        $collection = $this->repository->viewTransactionsInAdminCP($context, $id);

        return $this->success(new SubscriptionInvoiceTransactionCollection($collection));
    }

    public function viewShortTransactions(int $id): JsonResponse
    {
        $context = user();

        $data = $this->repository->viewInvoice($context, $id);

        return $this->success(new SimpleDetail($data));
    }
}
