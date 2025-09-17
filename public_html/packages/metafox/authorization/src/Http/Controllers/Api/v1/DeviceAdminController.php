<?php

namespace MetaFox\Authorization\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Authorization\Http\Resources\v1\Device\Admin\EditDeviceForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Authorization\Http\Resources\v1\Device\Admin\DeviceItem as Item;
use MetaFox\Authorization\Http\Resources\v1\Device\Admin\DeviceItemCollection as ItemCollection;
use MetaFox\Authorization\Http\Resources\v1\Device\Admin\DeviceDetail as Detail;
use MetaFox\Authorization\Repositories\DeviceAdminRepositoryInterface;
use MetaFox\Authorization\Http\Requests\v1\Device\Admin\IndexRequest;
use MetaFox\Authorization\Http\Requests\v1\Device\Admin\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Authorization\Http\Controllers\Api\DeviceAdminController::$controllers;
 */

/**
 * Class DeviceAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class DeviceAdminController extends ApiController
{
    /**
     * @var DeviceAdminRepositoryInterface
     */
    private DeviceAdminRepositoryInterface $repository;

    /**
     * DeviceAdminController Constructor.
     *
     * @param DeviceAdminRepositoryInterface $repository
     */
    public function __construct(DeviceAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest            $request
     * @return ItemCollection<Item>
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewDevices(user(), $params);

        return new ItemCollection($data);
    }

    public function edit(Request $request): JsonResponse
    {
        $form = resolve(EditDeviceForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest           $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateDevice(user(), $id, $params);

        return $this->success(new Detail($data));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteDevice(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('authorization::phrase.device_removed_successfully'));
    }
}
