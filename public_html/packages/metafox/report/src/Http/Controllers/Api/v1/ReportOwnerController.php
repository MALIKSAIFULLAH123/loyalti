<?php

namespace MetaFox\Report\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Report\Http\Requests\v1\ReportOwner\CreateFormRequest;
use MetaFox\Report\Http\Requests\v1\ReportOwner\IndexRequest;
use MetaFox\Report\Http\Requests\v1\ReportOwner\StoreRequest;
use MetaFox\Report\Http\Requests\v1\ReportOwner\UpdateRequest;
use MetaFox\Report\Http\Resources\v1\ReportOwner\ReporterCollection;
use MetaFox\Report\Http\Resources\v1\ReportOwner\ReportOwnerItemCollection as ItemCollection;
use MetaFox\Report\Http\Resources\v1\ReportOwner\StoreReportItemReportOwnerForm;
use MetaFox\Report\Models\ReportOwner;
use MetaFox\Report\Repositories\ReportOwnerRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Report\Http\Controllers\Api\ReportOwnerController::$controllers.
 */

/**
 * Class ReportOwnerController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group report
 */
class ReportOwnerController extends ApiController
{
    public ReportOwnerRepositoryInterface $repository;

    public function __construct(ReportOwnerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse reports.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException|AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->viewReports(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Post a report to owner.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createReportOwner(user(), $params);

        if (!$data) {
            return $this->error(__p(
                'report::phrase.already_reported_this_item_type',
                ['item_type' => $params['item_type']]
            ), 403);
        }

        return $this->success([], [], __p('report::phrase.successfully_reported'));
    }

    /**
     * Update a report.
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $this->repository->updateReportOwner(user(), $id, $params);

        return $this->success(['id' => $id], [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Get report form.
     *
     * @param CreateFormRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function form(CreateFormRequest $request): JsonResponse
    {
        $context = user();
        $data    = $request->validated();

        $report = new ReportOwner([
            'item_id'   => $data['item_id'],
            'item_type' => $data['item_type'],
        ]);

        $item = $report->item;
        if (null == $item) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'), 403);
        }

        $owner = $item->owner;
        if (null == $owner) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'), 403);
        }

        gate_authorize($context, 'reportToOwner', $item, $item);

        $data = new StoreReportItemReportOwnerForm($report);

        return $this->success($data, [], '');
    }

    /**
     * @throws AuthenticationException
     */
    public function listReporters(int $id): JsonResponse
    {
        $context = user();
        $data    = $this->repository->viewUsers($context, $id);

        return $this->success(new ReporterCollection($data), [], '');
    }
}
