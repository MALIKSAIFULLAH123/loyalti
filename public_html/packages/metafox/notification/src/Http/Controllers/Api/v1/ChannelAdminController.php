<?php

namespace MetaFox\Notification\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Notification\Http\Requests\v1\NotificationChannel\Admin\IndexRequest;
use MetaFox\Notification\Http\Resources\v1\NotificationChannel\Admin\NotificationChannelDetail as Detail;
use MetaFox\Notification\Http\Resources\v1\NotificationChannel\Admin\NotificationChannelItemCollection as ItemCollection;
use MetaFox\Notification\Repositories\ChannelAdminRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Notification\Http\Controllers\Api\ChannelAdminController::$controllers;
 */

/**
 * Class NotificationChannelAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class ChannelAdminController extends ApiController
{
    /**
     * NotificationChannelAdminController Constructor
     *
     * @param ChannelAdminRepositoryInterface $repository
     */
    public function __construct(protected ChannelAdminRepositoryInterface $repository) {}

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewChannels($params)->paginate();

        return new ItemCollection($data);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $item = $this->repository->toggleActive($id);

        Artisan::call('cache:reset');
        return $this->success([new Detail($item)], [], __p('core::phrase.already_saved_changes'));
    }
}
