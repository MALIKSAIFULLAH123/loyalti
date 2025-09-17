<?php

namespace MetaFox\Cache\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Cache\Http\Resources\v1\Admin\SelectCacheDriverForm;
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
 * | @link \MetaFox\Cache\Http\Controllers\Api\StoreAdminController::$controllers.
 */

/**
 * Class StoreAdminController.
 * @ignore
 */
class StoreAdminController extends ApiController
{
    /**
     * Browse item.
     *
     * @return mixed
     */
    public function index(): JsonResponse
    {
        $stores     = config('cache.stores', []);
        $hideInList = config('cache.system_stores', []);
        $data       = [];

        $ids = array_keys($stores);
        sort($ids);

        foreach ($ids as $id) {
            if (in_array($id, $hideInList)) {
                continue;
            }

            $data[] = $this->transformCacheStoreResource($id, $stores[$id]);
        }

        return $this->success($data);
    }

    public function create(): JsonResponse
    {
        $form = new SelectCacheDriverForm();

        return $this->success($form);
    }

    public function store(Request $request): JsonResponse
    {
        $params = $request->validate([
            'driver' => 'required|string',
            'name'   => 'required|string',
        ]);

        $nextAction = [
            'type'    => 'navigate',
            'payload' => [
                'url' => sprintf('/cache/store/edit/%s/%s', $params['driver'], $params['name']),
            ],
        ];

        return $this->success([], ['nextAction' => $nextAction]);
    }

    private function transformCacheStoreResource(string $id, ?array $config): array
    {
        $hasFromDrivers = resolve(DriverRepositoryInterface::class)
            ->getNamesHasHandlerClass('form-cache');

        $driver = $config['driver'] ?? 'unknown';

        $default   = Settings::get('cache.default');
        $canEdit   = in_array($driver, $hasFromDrivers);
        $isDefault = $default === $id;
        $canDelete = $canEdit && !$isDefault;
        $text      = __p('cache::phrase.cache_store_' . $driver . '_driver_guide');

        if (0 === mb_strpos(strtolower($id), 'throttling')) {
            $driver = 'redis';
            $text   = __p('cache::phrase.throttling_cache_store_redis_driver_guide');
        }

        return [
            'id'         => $id,
            'driver'     => $driver,
            'text'       => $text,
            'is_default' => $isDefault,
            'can_edit'   => $canEdit,
            'can_delete' => $canDelete,
            'links'      => [
                'editItem' => '/cache/store/edit/' . $driver . '/' . $id,
            ],
        ];
    }

    /**
     * @param  string       $driver
     * @param  string       $name
     * @return JsonResponse
     */
    public function edit(string $driver, string $name): JsonResponse
    {
        $config = Settings::get('cache.stores.' . $name, []);

        $class = null;

        try {
            $class = resolve('core.drivers')->getDriver('form-cache', "$driver.$name", 'admin');
        } catch (\Throwable) {
            //Just silent the error. No need to recover.
        }

        if (null === $class) {
            $class = resolve('core.drivers')->getDriver('form-cache', $driver, 'admin');
        }

        $form = new $class([
            'name'   => $name,
            'driver' => $driver,
            'value'  => $config,
        ]);

        return $this->success($form);
    }

    public function update(string $driver, string $name): JsonResponse
    {
        $parameters = [
            'driver' => $driver,
            'name'   => $name,
        ];

        $class = null;
        try {
            $class = resolve('core.drivers')->getDriver('form-cache', "$driver.$name", 'admin');
        } catch (\Throwable) {
            //Just silent the error. No need to recover.
        }

        if (null === $class) {
            $class = resolve('core.drivers')->getDriver('form-cache', $driver, 'admin');
        }

        $form = resolve($class);

        $data = app()->call([$form, 'validated'], $parameters);

        $settingName = 'cache.stores.' . $name;
        $configName  = 'cache.stores.' . $name;

        $data = Arr::add($data, 'selectable', true);

        Settings::updateSetting('cache', $settingName, $configName, null, $data, 'array', 0, 1);

        Artisan::call('cache:reset');

        $nextAction = [
            'type'    => 'navigate',
            'payload' => ['url' => '/cache/store/browse', 'replace' => true],
        ];

        return $this->success([], [
            'nextAction' => $nextAction,
        ], __p('core::phrase.save_changed_successfully'));
    }
}
