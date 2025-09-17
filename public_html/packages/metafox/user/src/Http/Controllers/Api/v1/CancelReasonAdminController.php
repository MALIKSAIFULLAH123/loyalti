<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\User\Http\Requests\v1\CancelReason\Admin\IndexRequest;
use MetaFox\User\Http\Requests\v1\CancelReason\Admin\StoreRequest;
use MetaFox\User\Http\Requests\v1\CancelReason\Admin\UpdateRequest;
use MetaFox\User\Http\Resources\v1\CancelReason\Admin\CancelReasonDetail as Detail;
use MetaFox\User\Http\Resources\v1\CancelReason\Admin\CancelReasonItem as Item;
use MetaFox\User\Http\Resources\v1\CancelReason\Admin\CancelReasonItemCollection as ItemCollection;
use MetaFox\User\Http\Resources\v1\CancelReason\Admin\CreateCancelReasonForm as CreateForm;
use MetaFox\User\Http\Resources\v1\CancelReason\Admin\EditCancelReasonForm as EditForm;
use MetaFox\User\Models\CancelReason;
use MetaFox\User\Repositories\CancelReasonAdminRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\CancelReasonAdminController::$controllers.
 */

/**
 * Class CancelReasonAdminController.
 *
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @admincp
 */
class CancelReasonAdminController extends ApiController
{
    /**
     * @var CancelReasonAdminRepositoryInterface
     */
    public CancelReasonAdminRepositoryInterface $repository;

    /**
     * CancelReasonAdminController constructor.
     *
     * @param CancelReasonAdminRepositoryInterface $repository
     */
    public function __construct(CancelReasonAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection<Item>
     * @group user
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewReasons(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $resource = $this->repository->createReason(user(), $params);

        $this->navigate($resource->admin_browse_url, true);
        Artisan::call('cache:reset');

        return $this->success(new Detail($resource), [], __p('user::phrase.reason_created_successfully'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateReason(user(), $id, $params);
        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('user::phrase.reason_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group user
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteReason(user(), $id);

        Artisan::call('cache:reset');
        return $this->success([
            'id' => $id,
        ], [], __p('user::phrase.reason_deleted_successfully'));
    }

    public function edit($id)
    {
        $resource = $this->repository->find($id);

        return new EditForm($resource);
    }

    public function create(): JsonResponse
    {
        $resource = new CancelReason();

        return $this->success(new CreateForm($resource));
    }

    /**
     * Active menu item.
     *
     * @param ActiveRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $isActive = $params['active'];
        $resource = $this->repository->update([
            'is_active' => $isActive,
        ], $id);

        $message = match ((bool) $isActive) {
            true    => __p('user::phrase.reason_was_activated_successfully'),
            default => __p('user::phrase.reason_was_deactivated_successfully'),
        };

        Artisan::call('cache:reset');
        return $this->success(new Detail($resource), [], $message);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids');

        $context = user();

        $this->repository->orderReasons($context, $orderIds);

        return $this->success([], [], __p('user::phrase.reasons_successfully_ordered'));
    }
}
