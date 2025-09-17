<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\ActivityPoint\Http\Requests\v1\PointPackage\IndexRequest;
use MetaFox\ActivityPoint\Http\Requests\v1\PointPackage\PurchaseRequest;
use MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\PointPackageDetail as Detail;
use MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\PointPackageItemCollection as ItemCollection;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Form\AbstractForm;
use MetaFox\Payment\Traits\Controller\HandleExtraPaymentParamsTrait;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\SEO\ActionMeta;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\ActivityPoint\Http\Controllers\Api\PointPackageController::$controllers;
 */

/**
 * Class PointPackageController.
 * @codeCoverageIgnore
 * @ignore
 * @authenticated
 * @group activitypoint
 */
class PointPackageController extends ApiController
{
    use HandleExtraPaymentParamsTrait;

    /**
     * @var PointPackageRepositoryInterface
     */
    private PointPackageRepositoryInterface $repository;

    /**
     * PointPackageController Constructor.
     *
     * @param PointPackageRepositoryInterface $repository
     */
    public function __construct(PointPackageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->viewPackages($context, $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function show(int $id): JsonResponse
    {
        $context = user();
        $data    = $this->repository->viewPackage($context, $id);

        return $this->success(new Detail($data));
    }

    /**
     * @throws AuthenticationException
     */
    public function purchase(PurchaseRequest $request, int $id): JsonResponse
    {
        $context = user();

        $params = array_merge($request->validated(), [
            'id' => $id,
        ]);

        $nextPaymentForm = ActivityPoint::getNextPaymentForm($context, $params);

        if ($nextPaymentForm instanceof AbstractForm) {
            return $this->success($nextPaymentForm, $nextPaymentForm->getMultiStepFormMeta());
        }

        $data = $this->repository->purchasePackage($context, $id, $params, $this->getExtraPaymentParams($context, $params));

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
