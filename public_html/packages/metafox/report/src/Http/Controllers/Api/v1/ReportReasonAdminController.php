<?php

namespace MetaFox\Report\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Report\Http\Requests\v1\ReportReason\Admin\StoreRequest;
use MetaFox\Report\Http\Requests\v1\ReportReason\Admin\UpdateRequest;
use MetaFox\Report\Http\Resources\v1\ReportReason\Admin\CreateReportReasonForm;
use MetaFox\Report\Http\Resources\v1\ReportReason\Admin\ReportReasonDetail as Detail;
use MetaFox\Report\Http\Resources\v1\ReportReason\Admin\ReportReasonItemCollection as ItemCollection;
use MetaFox\Report\Repositories\ReportReasonAdminRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Report\Http\Controllers\Api\ReportReasonAdminController::$controllers.
 */

/**
 * Class ReportReasonAdminController.
 *
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group report
 * @admincp
 */
class ReportReasonAdminController extends ApiController
{
    /**
     * @var ReportReasonAdminRepositoryInterface
     */
    private ReportReasonAdminRepositoryInterface $repository;

    /**
     * @param ReportReasonAdminRepositoryInterface $repository
     */
    public function __construct(ReportReasonAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display report reasons.
     *
     * @return JsonResource
     * @throws AuthorizationException|AuthenticationException
     */
    public function index(): JsonResource
    {
        $context = user();
        $data    = $this->repository->viewReasons($context);

        return new ItemCollection($data);
    }

    /**
     * Store a report reason.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $this->repository->createReason($context, $params);

        Artisan::call('cache:reset');

        $nextAction = [
            'type'    => 'navigate',
            'payload' => [
                'url'     => '/report/reason/browse',
                'replace' => true,
            ],
        ];

        Artisan::call('cache:reset');
        return $this->success([], [
            'nextAction' => $nextAction,
        ], __p('report::phrase.reason_created_successfully'));
    }

    /**
     * Update a report reason.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResource
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResource
    {
        $params  = $request->validated();
        $context = user();
        $data    = $this->repository->updateReason($context, $id, $params);

        Artisan::call('cache:reset');
        return new Detail($data);
    }

    /**
     * Remove a report reason.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();
        $this->repository->deleteReason($context, $id);

        return $this->success([], [], __p('report::phrase.reason_deleted_successfully'));
    }

    /**
     * Get create form.
     *
     * @return CreateReportReasonForm
     */
    public function create(): CreateReportReasonForm
    {
        return new CreateReportReasonForm();
    }

    /**
     * Get creation form.
     *
     * @return JsonResource
     */
    public function edit(): JsonResource
    {
        return new CreateReportReasonForm();
    }

    public function order(Request $request): JsonResponse
    {
        $context = user();
        $params  = $request->all();

        $this->repository->orderReasons($context, $params);

        return $this->success([], [], __p('report::phrase.reason_reorder_successfully'));
    }

    public function default(int $id): JsonResponse
    {
        $this->repository->getModel()->newQuery()
            ->where('is_default', 1)
            ->update([
                'is_default' => 0,
            ]);

        $item = $this->repository->find($id);
        $item->update(['is_default' => 1]);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('core::phrase.updated_successfully'));
    }
}
