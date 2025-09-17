<?php

namespace MetaFox\Report\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Report\Http\Requests\v1\ReportItem\Admin\IndexRequest;
use MetaFox\Report\Http\Resources\v1\ReportItem\Admin\ReportItemItemCollection as ItemCollection;
use MetaFox\Report\Repositories\ReportItemAdminRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Report\Http\Controllers\Api\ReportItemAdminController::$controllers.
 */

/**
 * Class ReportItemAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group report
 * @admincp
 */
class ReportItemAdminController extends ApiController
{
    public ReportItemAdminRepositoryInterface $repository;

    public function __construct(ReportItemAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse reports.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResource
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->viewReportItems($context, $params);

        return new ItemCollection($data);
    }
}
