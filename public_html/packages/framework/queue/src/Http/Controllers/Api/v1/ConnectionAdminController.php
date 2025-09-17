<?php

namespace MetaFox\Queue\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Queue\Http\Controllers\Api\ConnectionAdminController::$controllers.
 */

/**
 * Class ConnectionAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class ConnectionAdminController extends ApiController
{
    /**
     * Browse item.
     *
     * @return mixed
     */
    public function index(): JsonResponse
    {
        $connections = Settings::get('queue.connections');
        $data        = [];
        $keys        = array_keys($connections);
        sort($keys);

        foreach ($keys as $id) {
            $data[] = $this->transformQueueConnectionResource($id, $connections[$id]);
        }

        return $this->success($data);
    }

    public function transformQueueConnectionResource(string $id, ?array $config): array
    {
        $driver = $config['driver'] ?? 'unknown';

        $default        = Settings::get('queue.default');
        $hasFromDrivers = resolve(DriverRepositoryInterface::class)
            ->getNamesHasHandlerClass('form-queue');
        $canEdit   = in_array($driver, $hasFromDrivers);
        $isDefault = $default != $driver;
        $canDelete = $canEdit && !$isDefault;

        return [
            'id'         => $id,
            'driver'     => $driver,
            'text'       => __p('queue::phrase.queue_connection_' . $driver . '_driver_guide'),
            'is_default' => $isDefault,
            'can_edit'   => $canEdit,
            'can_delete' => $canDelete,
            'links'      => [
                'editItem' => '/queue/connection/edit/' . $driver . '/' . $id,
            ],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        return $this->success([]);
    }

    /**
     * @param  string       $driver
     * @param  string       $name
     * @param  Request      $request
     * @return JsonResponse
     */
    public function edit(string $driver, string $name, Request $request): JsonResponse
    {
        $class = resolve(DriverRepositoryInterface::class)
            ->getDriver('form-queue', $driver, 'admin');

        $form = new $class();

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }

    public function update(Request $request, string $name, string $driver): JsonResponse
    {
        $data = $request->all();

        $class = resolve(DriverRepositoryInterface::class)
            ->getDriver('form-queue', $driver, 'admin');

        $form = new $class();

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        if (method_exists($form, 'validated')) {
            $data = app()->call([$form, 'validated'], $request->route()->parameters());
        }

        $data['selectable'] = true;
        $response           = Settings::save([
            "queue.connections.$name" => $data,
        ]);

        Artisan::call('cache:reset');

        $nextAction = [
            'type'    => 'navigate',
            'payload' => ['url' => '/queue/connection/browse', 'replace' => true],
        ];

        return $this->success($response, [
            'nextAction' => $nextAction,
        ], __p('core::phrase.save_changed_successfully'));
    }
}
