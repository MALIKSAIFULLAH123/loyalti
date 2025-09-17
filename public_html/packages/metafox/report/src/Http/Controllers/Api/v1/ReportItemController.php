<?php

namespace MetaFox\Report\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Report\Http\Requests\v1\ReportItem\CreateFormRequest;
use MetaFox\Report\Http\Requests\v1\ReportItem\StoreRequest;
use MetaFox\Report\Http\Resources\v1\ReportItem\ReportItemDetail as Detail;
use MetaFox\Report\Http\Resources\v1\ReportItem\StoreReportItemForm;
use MetaFox\Report\Http\Resources\v1\ReportItem\StoreReportItemMobileForm;
use MetaFox\Report\Models\ReportItem;
use MetaFox\Report\Policies\ReportItemPolicy;
use MetaFox\Report\Repositories\ReportItemRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Report\Http\Controllers\Api\ReportItemController::$controllers;
 */

/**
 * Class ReportItemController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group report
 */
class ReportItemController extends ApiController
{
    /**
     * @var ReportItemRepositoryInterface
     */
    private ReportItemRepositoryInterface $repository;

    /**
     * @param ReportItemRepositoryInterface $repository
     */
    public function __construct(ReportItemRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store a new report.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws ValidatorException|AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createReport(user(), $params);

        if ($data == false) {
            return $this->error(__p('report::phrase.already_reported_this_item_type', ['item_type' => __p_type_key($params['item_type'])]), 403);
        }

        return $this->success(new Detail($data), [], __p('report::phrase.successfully_reported'));
    }

    /**
     * Get report form.
     *
     * @param  CreateFormRequest       $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function form(CreateFormRequest $request): JsonResponse
    {
        $context = user();
        $data    = $request->validated();
        policy_authorize(ReportItemPolicy::class, 'create', $context, $data);

        $reportItem            = new ReportItem();
        $reportItem->item_id   = $data['item_id'];
        $reportItem->item_type = $data['item_type'];
        $data                  = new StoreReportItemForm($reportItem);

        return $this->success($data, [], '');
    }

    /**
     * Get report form.
     *
     * @param  CreateFormRequest       $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function mobileForm(CreateFormRequest $request): JsonResponse
    {
        $context = user();
        $data    = $request->validated();
        policy_authorize(ReportItemPolicy::class, 'create', $context, $data);

        $reportItem            = new ReportItem();
        $reportItem->item_id   = $data['item_id'];
        $reportItem->item_type = $data['item_type'];
        $data                  = new StoreReportItemMobileForm($reportItem);

        return $this->success($data, [], '');
    }
}
