<?php

namespace MetaFox\Advertise\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Advertise\Http\Requests\v1\Placement\Admin\ActiveRequest;
use MetaFox\Advertise\Http\Requests\v1\Placement\Admin\DeleteRequest;
use MetaFox\Advertise\Http\Requests\v1\Placement\Admin\IndexRequest;
use MetaFox\Advertise\Http\Requests\v1\Placement\Admin\StoreRequest;
use MetaFox\Advertise\Http\Requests\v1\Placement\Admin\UpdateRequest;
use MetaFox\Advertise\Http\Resources\v1\Placement\Admin\CreatePlacementForm;
use MetaFox\Advertise\Http\Resources\v1\Placement\Admin\EditPlacementForm;
use MetaFox\Advertise\Http\Resources\v1\Placement\Admin\PlacementDetail as Detail;
use MetaFox\Advertise\Http\Resources\v1\Placement\Admin\PlacementItemCollection as ItemCollection;
use MetaFox\Advertise\Policies\PlacementPolicy;
use MetaFox\Advertise\Repositories\PlacementRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Advertise\Http\Controllers\Api\PlacementAdminController::$controllers;
 */

/**
 * Class PlacementAdminController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class PlacementAdminController extends ApiController
{
    /**
     * @var PlacementRepositoryInterface
     */
    private PlacementRepositoryInterface $repository;

    /**
     * PlacementAdminController Constructor.
     *
     * @param PlacementRepositoryInterface $repository
     */
    public function __construct(PlacementRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->viewPlacementsInAdminCP($context, $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->createPlacement($context, $params);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/advertise/placement/browse',
                ],
            ],
        ], __p('advertise::phrase.placement_successfully_created'));
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->updatePlacement($context, $id, $params);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('advertise::phrase.placement_successfully_updated'));
    }

    public function create(): JsonResponse
    {
        $context = user();

        policy_authorize(PlacementPolicy::class, 'create', $context);

        $form = new CreatePlacementForm();

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], request()->route()->parameters);
        }

        return $this->success($form);
    }

    public function edit(int $id): JsonResponse
    {
        $placement = resolve(PlacementRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(PlacementPolicy::class, 'update', $context, $placement);

        $form = new EditPlacementForm($placement);

        return $this->success($form);
    }

    public function delete(DeleteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $context = user();

        $this->repository->deletePlacement($context, Arr::get($data, 'id'), Arr::get($data, 'delete_option'), Arr::get($data, 'alternative_id'));

        Artisan::call('cache:reset');

        return $this->success([], [], __p('advertise::phrase.placement_successfully_deleted'));
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

        $isActive = Arr::get($data, 'active');

        $this->repository->activePlacement($context, $id, $isActive);

        $message = match ($isActive) {
            true  => __p('advertise::phrase.placement_successfully_activated'),
            false => __p('advertise::phrase.placement_successfully_deactivated'),
        };

        return $this->success([], [], $message);
    }
}
