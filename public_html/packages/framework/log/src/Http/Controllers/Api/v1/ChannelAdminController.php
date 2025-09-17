<?php

namespace MetaFox\Log\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Log\Http\Resources\v1\Admin\SelectLogDriver;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use RuntimeException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\LoggerAdminController::$controllers.
 */

/**
 * Class LoggerAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class ChannelAdminController extends ApiController
{
    /**
     * Browse item.
     *
     * @return mixed
     */
    public function index(): JsonResponse
    {
        $channels = config('logging.channels');

        $data = [];
        foreach ($channels as $id => $channel) {
            $data[] = $this->transformResource($id, $channel);
        }

        return $this->success($data);
    }

    /**
     * @param  string       $driver
     * @param  string       $name
     * @param  Request      $request
     * @return JsonResponse
     */
    public function update(string $driver, string $name, Request $request): JsonResponse
    {
        $config = Settings::get(sprintf('log.channels.%s', $name));

        $driverClass = resolve(DriverRepositoryInterface::class)
            ->getDriver('form-logger', $driver, 'admin');

        if (!$config) {
            $config = [];
        }

        $form = new $driverClass([
            'name'   => $name,
            'driver' => $driver,
            'value'  => $config,
        ]);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        if (!method_exists($form, 'validated')) {
            throw new RuntimeException(sprintf('Missing required method %s::validated', $driverClass));
        }

        $data = app()->call([$form, 'validated'], $request->route()->parameters());

        // write down data to other success state.

        $nextAction = [
            'type'    => 'navigate',
            'payload' => [
                'url' => '/log/channel/browse',
            ],
        ];

        $settingName = sprintf('log.channels.%s', $name);
        $configName  = sprintf('logging.channels.%s', $name);
        Settings::updateSetting('log', $settingName, $configName, '', $data, 'array', 0, 1);

        Artisan::call('cache:reset');

        return $this->success([], ['nextAction' => $nextAction], __p('core::phrase.updated_successfully'));
    }

    public function create(): JsonResponse
    {
        return $this->success(new SelectLogDriver());
    }

    /**
     * @param  string       $name
     * @return JsonResponse
     */
    public function destroy(string $name): JsonResponse
    {
        return $this->success([
            'id' => $name,
        ]);
    }

    public function edit(string $driver, string $name)
    {
        $config = Settings::get(sprintf('log.channels.%s', $name));

        $driverClass = resolve(DriverRepositoryInterface::class)
            ->getDriver('form-logger', $driver, 'admin');

        if (!$config) {
            $config = [];
        }

        return new $driverClass([
            'name'   => $name,
            'driver' => $driver,
            'value'  => $config,
        ]);
    }

    private function transformResource(string $id, mixed $channel): array
    {
        $driver         = $channel['driver'] ?? 'single';
        $supportDrivers = resolve(DriverRepositoryInterface::class)
            ->getNamesHasHandlerClass('form-logger');
        $canEdit = in_array($driver, $supportDrivers);

        return [
            'id'         => $id,
            'name'       => $id,
            'driver'     => $driver,
            'can_edit'   => $canEdit,
            'can_delete' => false,
            'links'      => [
                'editItem' => sprintf('/log/channel/edit/%s/%s', $driver, $id),
            ],
        ];
    }
}
