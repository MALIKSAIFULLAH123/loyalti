<?php

namespace MetaFox\Authorization\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Authorization\Http\Requests\v1\Device\StoreRequest;
use MetaFox\Authorization\Http\Resources\v1\Device\DeviceDetail as Detail;
use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Authorization\Http\Controllers\Api\DeviceController::$controllers;.
 */

/**
 * Class DeviceController.
 * @codeCoverageIgnore
 * @ignore
 */
class DeviceController extends ApiController
{
    /**
     * @var DeviceRepositoryInterface
     */
    private DeviceRepositoryInterface $repository;

    /**
     * DeviceController Constructor.
     *
     * @param DeviceRepositoryInterface $repository
     */
    public function __construct(DeviceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @bodyParam device_id string required Example: iPhone11
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        Arr::set($params, 'token_id', $context->token()?->id);

        $device = $this->repository->updateOrCreateDevice($context, $params);

        app('firebase.fcm')->addUserDeviceGroup(
            $context->entityId(),
            [
                'deviceId'  => $device->device_id,
                'deviceUid' => $device->device_uid,
            ],
            [$device->device_token],
            $device->platform
        );

        return $this->success(new Detail($device));
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function logoutDevice(): JsonResponse
    {
        $context = user();

        if (!$context instanceof \MetaFox\User\Models\User) {
            throw new AuthenticationException();
        }
        $tokenId = $context->token()?->id;

        $this->repository->logoutAllByUser($context, $tokenId);
        Artisan::call('cache:reset');

        return $this->success([], [], __p('authorization::phrase.logout_other_device_successfully'));
    }
}
