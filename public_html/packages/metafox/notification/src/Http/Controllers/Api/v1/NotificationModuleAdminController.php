<?php

namespace MetaFox\Notification\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Notification\Http\Requests\v1\NotificationModule\Admin\IndexRequest;
use MetaFox\Notification\Http\Requests\v1\NotificationModule\Admin\ToggleChannelRequest;
use MetaFox\Notification\Http\Resources\v1\NotificationModule\Admin\NotificationModuleDetail;
use MetaFox\Notification\Http\Resources\v1\NotificationModule\Admin\NotificationModuleItemCollection as ItemCollection;
use MetaFox\Notification\Repositories\NotificationModuleRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Notification\Http\Controllers\Api\NotificationModuleAdminController::$controllers;
 */

/**
 * Class NotificationModuleAdminController
 *
 * @codeCoverageIgnore
 * @ignore
 */
class NotificationModuleAdminController extends ApiController
{
    /**
     * @var NotificationModuleRepositoryInterface
     */
    private NotificationModuleRepositoryInterface $repository;

    /**
     * NotificationModuleAdminController Constructor
     *
     * @param NotificationModuleRepositoryInterface $repository
     */
    public function __construct(NotificationModuleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewModules($params);

        return new ItemCollection($data);
    }


    public function channel(ToggleChannelRequest $request): JsonResponse
    {
        $channel = $request->validated('channel');
        $module  = $request->validated('id');
        $active  = $request->validated('active', 0);

        $data = $this->repository->toggleChannel($module, $channel, $active);

        if ($module) {
            // only reset cache if a new module is created, otherwise it's not necessary
            Artisan::call('cache:reset');
        }

        return $this->success(new NotificationModuleDetail($data), [], __p('core::phrase.updated_successfully'));
    }
}
