<?php

namespace MetaFox\TourGuide\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\TourGuide\Http\Resources\v1\Step\Admin\StepItemCollection as ItemCollection;
use MetaFox\TourGuide\Http\Resources\v1\Step\Admin\StepItem as Item;
use MetaFox\TourGuide\Http\Resources\v1\Step\Admin\UpdateStepForm;
use MetaFox\TourGuide\Repositories\StepRepositoryInterface;
use MetaFox\TourGuide\Http\Requests\v1\Step\Admin\IndexRequest;
use MetaFox\TourGuide\Http\Requests\v1\Step\Admin\UpdateRequest;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\TourGuide\Http\Controllers\Api\StepAdminController::$controllers;.
 */

/**
 * Class StepAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class StepAdminController extends ApiController
{
    /**
     * @var StepRepositoryInterface
     */
    private StepRepositoryInterface $repository;

    /**
     * StepAdminController Constructor.
     *
     * @param StepRepositoryInterface $repository
     */
    public function __construct(StepRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  IndexRequest   $request
     * @return ItemCollection
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->viewSteps($params);

        return new ItemCollection($data);
    }

    /**
     * @param  int            $id
     * @return UpdateStepForm
     */
    public function edit(int $id): UpdateStepForm
    {
        $resource = $this->repository->find($id);

        return new UpdateStepForm($resource);
    }

    /**
     * @param  UpdateRequest $request
     * @param  int           $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $data = $this->repository->updateStep($id, $params);

        Artisan::call('cache:reset');

        return $this->success(
            new Item($data),
            [],
            __p('tourguide::phrase.tour_guide_step_was_updated_successfully')
        );
    }

    /**
     * @param  ActiveRequest $request
     * @param  int           $id
     * @return JsonResponse
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params   = $request->validated();
        $isActive = Arr::get($params, 'active', false);

        $this->repository->updateActive($id, $isActive);

        return $this->success([
            'id'        => $id,
            'is_active' => (int) $isActive,
        ], [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * @param  Request      $request
     * @return JsonResponse
     */
    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids');

        $this->repository->orderSteps($orderIds);

        return $this->success(
            [],
            [],
            __p('tourguide::phrase.tour_guide_step_was_order_successfully')
        );
    }

    /**
     * @param  int          $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return $this->success([
            'id' => $id,
        ], [], __p('tourguide::phrase.tour_guide_step_was_deleted_successfully'));
    }
}
