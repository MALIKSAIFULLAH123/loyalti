<?php

namespace MetaFox\Profile\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Profile\Http\Requests\v1\Field\Admin\IndexRequest;
use MetaFox\Profile\Http\Requests\v1\Field\Admin\StoreRequest;
use MetaFox\Profile\Http\Requests\v1\Field\Admin\UpdateRequest;
use MetaFox\Profile\Http\Resources\v1\Field\Admin\CreateFieldForm;
use MetaFox\Profile\Http\Resources\v1\Field\Admin\DuplicateFieldForm;
use MetaFox\Profile\Http\Resources\v1\Field\Admin\EditFieldForm;
use MetaFox\Profile\Http\Resources\v1\Field\Admin\FieldDetail as Detail;
use MetaFox\Profile\Http\Resources\v1\Field\Admin\FieldItemCollection as ItemCollection;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Profile\Http\Controllers\Api\FieldAdminController::$controllers;.
 */

/**
 * class FieldAdminController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class FieldAdminController extends ApiController
{
    /**
     * @var FieldRepositoryInterface
     */
    private FieldRepositoryInterface $repository;

    /**
     * FieldAdminController Constructor.
     *
     * @param FieldRepositoryInterface $repository
     */
    public function __construct(FieldRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->viewFields($params);

        return new ItemCollection($data);
    }

    public function create(): JsonResponse
    {
        $form = new CreateFieldForm();

        return $this->success($form);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $data = $this->repository->createField($params);

        Artisan::call('cache:reset');

        $this->navigate($data->admin_browse_url);

        $message = __p('profile::phrase.custom_field_has_been_created_successfully');

        return $this->success(new Detail($data), [], $message);
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    public function edit(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        $form = new EditFieldForm($item);

        return $this->success($form);
    }

    public function duplicate(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        $form = new DuplicateFieldForm($item);

        return $this->success($form);
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateField($params, $id);

        Artisan::call('cache:reset');

        $this->navigate($data->admin_browse_url);

        $message = __p('profile::phrase.custom_field_has_been_updated_successfully');

        return $this->success(new Detail($data), [], $message);
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var Field $item */
        $item = $this->repository->find($id);
        $item->delete();

        Artisan::call('cache:reset');

        $message = __p('profile::phrase.custom_field_has_been_deleted_successfully');

        return $this->success(['id' => $id], [], $message);
    }

    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids');

        $this->repository->orderFields($orderIds);

        return $this->success([], [], __p('profile::phrase.fields_successfully_ordered'));
    }

    /**
     * Update active status.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $params = ['is_active' => $request->get('active')];
        $item   = $this->repository->toggleActive($id, $params);

        return $this->success(new Detail($item), [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * Update active status.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function toggleRegister(Request $request, int $id): JsonResponse
    {
        $params = ['is_register' => $request->get('active')];
        $item   = $this->repository->toggleActive($id, $params);

        return $this->success(new Detail($item), [], __p('core::phrase.already_saved_changes'));
    }
}
