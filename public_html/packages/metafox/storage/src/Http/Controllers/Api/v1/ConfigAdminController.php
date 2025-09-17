<?php

namespace MetaFox\Storage\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Storage\Http\Requests\v1\Config\Admin\DeleteRequest;
use MetaFox\Storage\Http\Requests\v1\Config\Admin\StoreRequest;
use MetaFox\Storage\Http\Resources\v1\Admin\SelectDiskDriverForm;
use MetaFox\Storage\Jobs\DeleteStorageFilesJob;
use MetaFox\Storage\Models\Disk;
use MetaFox\Storage\Models\StorageFile;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Storage\Http\Controllers\Api\DiskAdminController::$controllers.
 */

/**
 * Class DiskAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class ConfigAdminController extends ApiController
{
    public function index(): JsonResponse
    {
        $data = Settings::get('storage.filesystems.disks');

        $disks = [];

        if (is_array($data)) {
            foreach ($data as $id => $value) {
                if ($value['driver'] === 'alias') {
                    continue;
                }

                $disks[] = $this->transformDiskResource($id, $value);
            }
        }

        return $this->success($disks);
    }

    public function transformDiskResource(mixed $id, ?array $data): array
    {
        $name          = sprintf('%s', $id);
        $status        = '';
        $basePath      = '/';
        $baseUrl       = '';
        $driver        = $data['driver'] ?? null;
        $appRoot       = base_path();
        $defaultDiskId = Settings::get('storage.filesystems.default');

        try {
            $disk     = Storage::build($data);
            $basePath = $disk->path('filename');
            $baseUrl  = $disk->url('filename');
        } catch (\Exception $exception) {
            $status = $exception->getMessage();
        }

        if ($driver === 'local' && Str::startsWith($basePath, $appRoot)) {
            $basePath = '.' . Str::substr($basePath, strlen($appRoot));
        }

        $isDefault     = $name == $defaultDiskId;
        $isEditable    = $driver !== 'local';
        $isSystem      = in_array($name, ['local', 'public']);
        $fileBeingUsed = StorageFile::query()->where('target', $name)->first();
        $isBeingUsed   = $fileBeingUsed instanceof StorageFile;

        return [
            'id'          => $name,
            'name'        => $name,
            'driver'      => $driver,
            'title'       => $data['title'] ?? 'unknown',
            'base_path'   => substr($basePath, 0, -9),
            'base_url'    => substr($baseUrl, 0, -9),
            'disk_status' => $status,
            'can_edit'    => $isEditable,
            'can_delete'  => !$isSystem && !$isDefault,
            'is_system'   => $isSystem,
            'is_default'  => $isDefault,
            'links'       => [
                'edit' => sprintf('/storage/option/edit/%s/%s', $driver, $id),
            ],
        ];
    }

    /**
     * Delete item.
     *
     * @param DeleteRequest $request
     * @param string        $disk
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(DeleteRequest $request, string $disk): JsonResponse
    {
        $params        = $request->validated();
        $defaultDiskId = Settings::get('storage.filesystems.default');
        $isRemove      = Arr::get($params, 'is_remove');

        if (in_array($disk, ['local', 'public']) || $disk == $defaultDiskId) {
            throw new AuthorizationException();
        }

        $isBeingUsed = StorageFile::query()->where('target', $disk)->first();
        if ($isBeingUsed instanceof StorageFile && $isRemove) {
            DeleteStorageFilesJob::dispatch(['target' => $disk]);
        }

        $name = sprintf('storage.filesystems.disks.%s', $disk);

        Settings::destroy('storage', [$name]);

        Disk::query()->where('target', $disk)->each(function (Disk $model) use ($defaultDiskId) {
            $model->fill(['target' => $defaultDiskId])->save();
        });
        Artisan::call('cache:reset');

        // try to destroy disk
        return $this->success([
            'id' => $disk,
        ]);
    }

    public function create(): JsonResponse
    {
        return $this->success(new SelectDiskDriverForm());
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $driver = $params['driver'];
        $id     = $params['id'];

        return $this->success([], [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'     => sprintf('/storage/option/edit/%s/%s', $driver, $id),
                    'replace' => false,
                ],
            ],
        ]);
    }

    public function updateByDisk(string $disk)
    {
        $config = Settings::get(sprintf('storage.filesystems.disks.%s', $disk));

        $driver = $config['driver'] ?? 'unknown';

        $driverClass = resolve(DriverRepositoryInterface::class)
            ->getDriver('form-storage', $driver, 'admin');

        if (!$config) {
            $config = [];
        }

        return new $driverClass([
            'id'     => $disk,
            'driver' => $driver,
            'value'  => $config,
        ]);
    }

    public function edit(string $driver, string $disk)
    {
        $config = Settings::get(sprintf('storage.filesystems.disks.%s', $disk));

        if (!$driver) {
            $driver = $config['driver'] ?? 'unknown';
        }

        if ($driver === 'local') {
            throw new AuthorizationException();
        }

        $driverClass = resolve(DriverRepositoryInterface::class)
            ->getDriver('form-storage', $driver, 'admin');

        if (!$config) {
            $config = [];
        }

        return new $driverClass([
            'id'     => $disk,
            'driver' => $driver,
            'value'  => $config,
        ]);
    }

    public function update(string $driver, string $disk, Request $request): JsonResponse
    {
        $config = Settings::get(sprintf('filesystems.disks.%s', $disk));

        $driverClass = resolve(DriverRepositoryInterface::class)
            ->getDriver('form-storage', $driver, 'admin');

        if (!$config) {
            $config = [];
        }

        $form = new $driverClass([
            'id'     => $disk,
            'driver' => $driver,
            'value'  => $config,
        ]);

        if (method_exists($form, 'validated')) {
            // forward to dependency injection
            try {
                $config = app()->call([$form, 'validated'], $request->route()->parameters());
            } catch (\Throwable) {
                //Silent the errors and just return.
                return $this->error(__p('storage::phrase.invalid_configuration'));
            }
        }

        $driver = $config['driver'] ?? null;

        if ($driver === 'local') {
            throw new AuthorizationException();
        }

        $name = sprintf('storage.filesystems.disks.%s', $disk);

        $configName = sprintf('filesystems.disks.%s', $disk);

        Settings::updateSetting('storage', $name, $configName, '', $config, 'array', false, true);

        Artisan::call('cache:reset');

        $nextAction = [
            'type'    => 'navigate',
            'payload' => [
                'url'     => '/storage/option/browse',
                'replace' => true,
            ],
        ];

        $message = __p('core::phrase.updated_successfully');

        return $this->success($this->transformDiskResource($disk, $config), ['nextAction' => $nextAction], $message);
    }
}
