<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Http\Requests\v1\PackageTransaction\IndexRequest;
use MetaFox\ActivityPoint\Http\Requests\v1\PointPackage\PurchaseRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction\PackageTransactionItemCollection as ItemCollection;
use MetaFox\ActivityPoint\Policies\PackagePurchasePolicy;
use MetaFox\ActivityPoint\Repositories\PurchasePackageRepositoryInterface;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Constants;
use MetaFox\Payment\Traits\Controller\HandleExtraPaymentParamsTrait;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\SEO\ActionMeta;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\ActivityPoint\Http\Controllers\Api\PackageTransactionController::$controllers;
 */

/**
 * Class PackageTransactionController.
 * @codeCoverageIgnore
 * @ignore
 * @authenticated
 * @group activitypoint
 */
class PackageTransactionController extends ApiController
{
    /**
     * PackageTransactionController Constructor.
     *
     * @param PurchasePackageRepositoryInterface $repository
     */
    public function __construct(protected PurchasePackageRepositoryInterface $repository) {}

    use HandleExtraPaymentParamsTrait;

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResource
    {
        $context = user();
        $params  = $request->validated();
        $limit   = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $table   = $this->repository->getModel()->getTable();
        $data    = $this->repository->viewTransactions($context, $params)->paginate($limit, ["$table.*"]);

        return new ItemCollection($data);
    }

    /**
     * @param PurchaseRequest $request
     * @param int             $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function pay(PurchaseRequest $request, int $id): JsonResponse
    {
        $context  = user();
        $params   = $request->validated();
        $purchase = $this->repository->find($id);

        policy_authorize(PackagePurchasePolicy::class, 'pay', $context, $purchase);

        Arr::set($params, 'id', $purchase->package->entityId());
        Arr::set($params, 'action', apiUrl('activitypoint.package-transaction.payment', ['id' => $id]));
        Arr::set($params, 'method', Constants::METHOD_POST);

        $gatewayId       = Arr::get($params, 'payment_gateway');
        $nextPaymentForm = ActivityPoint::getNextPaymentForm($context, $params);

        if ($nextPaymentForm instanceof AbstractForm) {
            return $this->success($nextPaymentForm, $nextPaymentForm->getMultiStepFormMeta());
        }

        $extraParams = $this->getExtraPaymentParams($context, $params);
        $data        = $this->repository->payInvoice($context, $purchase->entityId(), $gatewayId, $extraParams);

        return $this->success($data, $this->getMetaData());
    }

    protected function getMetaData(): array
    {
        if (MetaFox::isMobile()) {
            return [];
        }

        $actionMeta = new ActionMeta();

        $actionMeta->continueAction()->type(MetaFoxConstant::TYPE_MULTISTEP_FORM_DONE);

        return $actionMeta->toArray();
    }
}
