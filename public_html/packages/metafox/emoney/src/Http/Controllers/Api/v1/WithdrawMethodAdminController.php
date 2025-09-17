<?php

namespace MetaFox\EMoney\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\WithdrawMethod;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\EMoney\Http\Resources\v1\WithdrawMethod\Admin\WithdrawMethodItemCollection as ItemCollection;
use MetaFox\EMoney\Repositories\WithdrawMethodRepositoryInterface;
use MetaFox\EMoney\Http\Requests\v1\WithdrawMethod\Admin\IndexRequest;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\EMoney\Http\Controllers\Api\WithdrawMethodAdminController::$controllers;
 */

/**
 * Class WithdrawMethodAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class WithdrawMethodAdminController extends ApiController
{
    /**
     * @var WithdrawMethodRepositoryInterface
     */
    private WithdrawMethodRepositoryInterface $repository;

    /**
     * WithdrawMethodAdminController Constructor.
     *
     * @param WithdrawMethodRepositoryInterface $repository
     */
    public function __construct(WithdrawMethodRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $data = $this->repository->viewMethods()->values();

        return new ItemCollection($data);
    }

    /**
     * @param  ActiveRequest $request
     * @param  string        $service
     * @return JsonResponse
     */
    public function toggleActive(ActiveRequest $request, string $service): JsonResponse
    {
        $data = $request->validated();

        $active = (bool) Arr::get($data, 'active');

        $this->repository->activateMethod($service, $active);

        $message = match ($active) {
            true    => __p('ewallet::admin.provider_was_activated_successfully'),
            default => __p('ewallet::admin.provider_was_deactivated_successfully'),
        };

        return $this->success([
            'id'            => $service,
            'module_name'   => Emoney::getAppAlias(),
            'resource_name' => WithdrawMethod::ENTITY_TYPE,
            'is_active'     => $active,
        ], [], $message);
    }
}
