<?php

namespace MetaFox\TourGuide\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\TourGuide\Http\Requests\v1\TourGuide\Admin\BatchDeleteRequest;
use MetaFox\TourGuide\Http\Resources\v1\TourGuide\Admin\TourGuideItemCollection as ItemCollection;
use MetaFox\TourGuide\Http\Resources\v1\TourGuide\Admin\TourGuideItem as Item;
use MetaFox\TourGuide\Http\Resources\v1\TourGuide\Admin\UpdateTourGuideForm;
use MetaFox\TourGuide\Repositories\HiddenRepositoryInterface;
use MetaFox\TourGuide\Repositories\TourGuideAdminRepositoryInterface;
use MetaFox\TourGuide\Http\Requests\v1\TourGuide\Admin\IndexRequest;
use MetaFox\TourGuide\Http\Requests\v1\TourGuide\Admin\UpdateRequest;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\TourGuide\Http\Controllers\Api\TourGuideAdminController::$controllers;.
 */

/**
 * Class TourGuideAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class TourGuideAdminController extends ApiController
{
    private TourGuideAdminRepositoryInterface $repository;
    private HiddenRepositoryInterface $hiddenRepository;

    public function __construct(
        TourGuideAdminRepositoryInterface $repository,
        HiddenRepositoryInterface $hiddenRepository,
    ) {
        $this->repository       = $repository;
        $this->hiddenRepository = $hiddenRepository;
    }

    /**
     * @param IndexRequest $request
     * @return ItemCollection
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->viewTourGuides($params);

        return new ItemCollection($data);
    }

    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $resource = $this->repository->updateTourGuide($id, $params);

        return $this->success(
            new Item($resource),
            [],
            __p('tourguide::phrase.tour_guide_was_updated_successfully')
        );
    }

    /**
     * @param ActiveRequest $request
     * @param int $id
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
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return $this->success([
            'id' => $id,
        ], [], __p('tourguide::phrase.tour_guide_was_deleted_successfully'));
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function reset(int $id): JsonResponse
    {
        $this->hiddenRepository->deleteHiddenByTourGuideId($id);

        return $this->success([], [], __p('tourguide::phrase.tour_guide_was_reset_successfully'));
    }

    /**
     * @param BatchDeleteRequest $request
     * @return JsonResponse
     */
    public function batchDelete(BatchDeleteRequest $request): JsonResponse
    {
        $params = $request->validated();
        $ids    = Arr::get($params, 'id', []);

        $this->repository->batchDelete($ids);

        return $this->success([], [], __p('tourguide::phrase.tour_guide_s_was_deleted_successfully'));
    }

    /**
     * @param int $id
     * @return UpdateTourGuideForm
     */
    public function edit(int $id): UpdateTourGuideForm
    {
        $resource = $this->repository->find($id);

        return new UpdateTourGuideForm($resource);
    }
}
