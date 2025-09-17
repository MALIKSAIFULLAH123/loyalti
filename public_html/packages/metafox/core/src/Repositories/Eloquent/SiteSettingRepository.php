<?php

namespace MetaFox\Core\Repositories\Eloquent;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Constants;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\SiteSettingRepositoryInterface;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\PackageManager;

/**
 * Class SiteSettingRepository.
 */
class SiteSettingRepository implements SiteSettingRepositoryInterface
{
    /**
     * Keep associate `key`=> `id`.
     *
     * @code
     * [ 'blog.privacy_id'=>true,
     *  'core.setting_version_id'=>4,
     *  ''
     * ]
     * @encode
     * @var array<string, mixed>
     */
    protected array $cachedValueBag = [];

    public function __construct()
    {
        $this->initialize();
    }

    private function initialize(): void
    {
        try {
            $this->loadCachedValueBag();
        } catch (\Throwable) {
        }
    }

    /**
     * @param mixed  $value
     * @param string $type
     *
     * @return mixed
     */
    private function actualValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            MetaFoxDataType::BOOLEAN => (bool) $value,
            MetaFoxDataType::STRING  => (string) $value,
            MetaFoxDataType::INTEGER => (int) $value,
            default                  => $value,
        };
    }

    public function createSetting(
        string $module,
        string $name,
        ?string $configName,
        ?string $envVar,
        mixed $value,
        string $type,
        bool $public,
        bool $auto
    ): bool {
        $model = $this->getByName($name);

        if (!$model) {
            $model = SiteSetting::query()->create([
                'module_id'   => $module,
                'name'        => $name,
                'config_name' => $configName,
            ]);
        }

        $model->fill([
            'package_id'    => PackageManager::getByAlias($module),
            'value_default' => $value,
            'env_var'       => $envVar,
            'type'          => $type,
            'is_auto'       => $auto,
            'is_public'     => $public,
        ]);

        $model->save();

        return true;
    }

    public function updateSetting(
        string $module,
        string $name,
        ?string $configName,
        ?string $envVar,
        mixed $value,
        string $type,
        bool $public,
        bool $auto
    ): bool {
        $model = $this->getByName($name);

        if (!$model) {
            $model = SiteSetting::query()->create([
                'module_id'     => $module,
                'name'          => $name,
                'value_default' => $value,
            ]);
        }

        $model->fill([
            'package_id'   => PackageManager::getByAlias($module),
            'name'         => $name,
            'env_var'      => $envVar,
            'value_actual' => $value,
            'config_name'  => $configName,
            'type'         => $type,
            'is_auto'      => $auto,
            'is_public'    => $public,
        ]);

        $model->save();

        return true;
    }

    public function setupPackageSettings(string $module, array $settings): array
    {
        $response = [];

        foreach ($settings as $name => $data) {
            // delete dirty settings.
            if ($data['is_deleted'] ?? false) {
                SiteSetting::query()->where([
                    'name' => $name,
                ])->delete();
                continue;
            }

            $configName   = $data['config_name'] ?? null;
            $envVar       = $data['env_var'] ?? null;
            $valueDefault = null;
            if ($configName) {
                $valueDefault = config($configName);
            }
            if (null === $valueDefault && $envVar) {
                $valueDefault = env($envVar);
            }

            if (null === $valueDefault) {
                $valueDefault = $data['value'] ?? null;
            }

            // force map to another modules. by 'module_id'
            $name = sprintf('%s.%s', $data['module_id'] ?? $module, $name);
            // get by default value instead of env_value to correct typeof
            $type        = $data['type'] ?? gettype($valueDefault ?? 'string');
            $actualValue = $this->actualValue($valueDefault, $type);
            $this->createSetting(
                $module,
                $name,
                $configName,
                $envVar,
                $actualValue,
                $type,
                $data['is_public'] ?? true,
                $data['is_auto'] ?? true
            );

            Arr::set($response, $name, $actualValue);
        }

        return $response;
    }

    public function destroy(string $module, ?array $names = null): bool
    {
        if (null !== $names && empty($names)) {
            return true;
        }

        $query = SiteSetting::query();

        $query = $query->where('module_id', '=', $module);

        if (null !== $names) {
            $query = $query->whereIn('name', $names);
        }

        return (bool) $query->delete();
    }

    public function has(string $key): bool
    {
        return Arr::has($this->cachedValueBag, $key);
    }

    public function get(string $key, $default = null)
    {
        $value = Arr::get($this->cachedValueBag, $key, $default);

        if (__is_phrase($value)) {
            return __p($value);
        }

        return $value;
    }

    private function getByName(string $name): ?SiteSetting
    {
        /** @var SiteSetting $model */
        $model = SiteSetting::query()
            ->where('name', '=', $name)
            ->first();

        return $model;
    }

    /**
     * Calculate dotted key of array which value is array.
     *
     * @param  string $prefix
     * @param  array  $values
     * @param  array  $keys
     * @return void
     */
    private function getArrayNestedKeys(string $prefix, array $values, array &$keys): void
    {
        foreach ($values as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $newKey = $prefix ? $prefix . '.' . $key : $key;
            $keys[] = $newKey;
            $this->getArrayNestedKeys($newKey, $value, $keys);
        }
    }

    public function save(array $values): array
    {
        return $this->saveAndCollectModifiedSettings($values);
    }

    public function saveAndCollectModifiedSettings(array $values, ?array &$modified = []): array
    {
        $response = [];

        $modified = Arr::wrap($modified);

        $keys = [];

        $this->getArrayNestedKeys('', $values, $keys);

        $typeArray = SiteSetting::query()
            ->whereIn('name', $keys)
            ->where('type', '=', 'array')
            ->pluck('name');

        foreach ($typeArray as $name) {
            /** @var SiteSetting|null $model */
            $model = $this->getByName($name);

            if (!$model) {
                continue;
            }

            $value               = Arr::get($values, $name);
            $model->value_actual = $value;
            $model->save();

            Arr::set($values, $name, false);

            if ($model->wasChanged('value_actual')) {
                $modified['all'][$name] = $value;

                if ($model->isPrivate()) {
                    $modified['private'][$name] = $value;
                }
            }
        }

        $dotted = Arr::dot($values);

        foreach ($dotted as $settingName => $value) {
            /** @var SiteSetting|null $model */
            $model = $this->getByName($settingName);

            if (!$model instanceof SiteSetting) {
                continue;
            }

            // process array merged before
            if ($model->type === MetaFoxDataType::ARRAY) {
                continue;
            }

            $value               = $this->actualValue($value, $model->type);
            $model->value_actual = $value;
            $model->save();

            Arr::set($response, $settingName, $value);

            if ($model->wasChanged('value_actual')) {
                $modified['all'][$settingName] = $value;

                if ($model->isPrivate()) {
                    $modified['private'][$settingName] = $value;
                }
            }
        }

        return $response;
    }

    public function reset(string $module, ?array $names = null): bool
    {
        if (null !== $names && empty($names)) {
            return true;
        }

        $query = SiteSetting::query();

        $query = $query->where('module_id', '=', $module);

        if (null !== $names) {
            $query = $query->whereIn('name', $names);
        }

        return (bool) $query->update(['value_actual' => null]);
    }

    public function refresh(): void
    {
        try {
            $this->save([
                'core.setting_version_id' => Carbon::now()->timestamp,
                'core.setting_updated_at' => Carbon::now(),
            ]);
            $this->loadCachedValueBag();
        } catch (Exception $e) {
            Log::channel('dev')->info($e->getMessage());
        }
    }

    public function versionId(): int
    {
        return (int) $this->get('core.setting_version_id');
    }

    public function updatedAt(): string
    {
        return (string) $this->get('core.setting_updated_at');
    }

    public function bootingKernelConfigs(): void
    {
        $versionId = self::versionId();

        if (config('core.setting_version_id') == $versionId) {
            return;
        }

        Log::channel('dev')->debug('loadConfigValues', [
            '$versionId'              => $versionId,
            'core.setting_version_id' => config('core.setting_version_id'),
        ]);

        $config = $this->loadConfigValues();

        Config::set($config);
    }

    public function loadConfigValues(): array
    {
        // should run php artisan config:cache to speed up optimization

        /** @var SiteSetting[]|Collection $settings */
        $settings = SiteSetting::query()
            ->whereNotNull('config_name')
            ->orderBy('config_name', 'asc')
            ->cursor();

        $arr = [];

        foreach ($settings as $setting) {
            $key   = $setting->config_name;
            $value = $setting->getValue();
            if ($key && !empty($value)) {
                $arr[$key] = $value;
            }
        }

        $arr['core.setting_version_id'] = $this->versionId();

        return $arr;
    }

    private function loadCachedValueBag(): void
    {
        $this->cachedValueBag = localCacheStore()->rememberForever(
            __METHOD__,
            function () {
                $aliases = resolve('core.packages')->getActivePackageAliases();

                /** @var SiteSetting[]|Collection $settings */
                $settings = SiteSetting::query()
                    ->whereIn('module_id', $aliases)
                    ->get([
                        'name',
                        'type',
                        'value_actual',
                        'value_default',
                    ]);

                $arr = [];

                foreach ($settings as $setting) {
                    Arr::set($arr, $setting->name, $setting->getValue());
                }

                return $arr;
            }
        );
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    private function getValueFromDatabase(int $id): mixed
    {
        /** @var SiteSetting $setting */
        $setting = SiteSetting::query()->find($id, ['value_actual', 'value_default']);

        return $setting?->getValue();
    }

    public function keys(): array
    {
        return array_keys($this->cachedValueBag);
    }

    public function getSiteSettings(string $for, bool $loadFromDriver): array
    {
        $aliases = resolve('core.packages')->getActivePackageAliases();

        $bag = [];

        /** @var SiteSetting[]|Collection $settings */
        $settings = SiteSetting::query()
            ->whereIn('module_id', $aliases)
            ->where('is_public', '=', 1)
            ->get(['type', 'name', 'value_actual', 'value_default']);

        foreach ($aliases as $alias) {
            Arr::set($bag, $alias, new \stdClass());
        }

        Arr::set($bag, 'app.env', config('app.env'));

        foreach ($settings as $setting) {
            $settingValue = $setting->getValue();

            if (__is_phrase($settingValue)) {
                $settingValue = __p($settingValue);
            }

            Arr::set($bag, $setting->name, $settingValue);

            /**@deprecated V5.1.8 remove this */
            if (in_array($setting->name, ['event.enable_map', 'marketplace.enable_map'])) {
                if ($this->get('core.google.google_map_api_key') == null) {
                    Arr::set($bag, $setting->name, false);
                }
            }
        }

        if ($loadFromDriver) {
            $this->loadFromDriver($for, $bag);
        }

        return $bag;
    }

    private function loadFromDriver(string $for, array &$result): void
    {
        $drivers = resolve(DriverRepositoryInterface::class)
            ->loadDrivers(Constants::DRIVER_TYPE_PACKAGE_SETTING, null);

        $method = 'getWebSettings';

        if ($for === 'mobile') {
            $method = 'getMobileSettings';
        }

        foreach ($drivers as $driver) {
            [, $class, , , $packageId] = $driver;

            if (!class_exists($class)) {
                continue;
            }

            $setting = resolve($class);

            if (!method_exists($setting, $method)) {
                continue;
            }

            $alias = PackageManager::getAliasFor($packageId, $for);

            $data = app()->call([$setting, $method]);

            foreach ($data as $name => $value) {
                Arr::set($result, sprintf('%s.%s', $alias, $name), $value);
            }
        }
    }

    public function mockValues(array $values)
    {
        foreach ($values as $name => $value) {
            Arr::set($this->cachedValueBag, $name, $value);
        }
    }

    /**
     * Export private settings to build frontend.
     */
    public function getPrivateEnvironments(): array
    {
        $aliases = resolve('core.packages')->getActivePackageAliases();

        $bag = [];

        /** @var SiteSetting[]|Collection $settings */
        $settings = SiteSetting::query()
            ->whereIn('module_id', $aliases)
            ->where('is_public', '=', 0)
            ->get(['type', 'name', 'value_actual', 'value_default']);

        foreach ($aliases as $alias) {
            Arr::set($bag, $alias, new \stdClass());
        }

        foreach ($settings as $setting) {
            Arr::set($bag, $setting->name, $setting->getValue());
        }

        $bag      = Arr::dot($bag);
        $response = [];

        foreach ($bag as $key => $value) {
            if (null === $value || $value === '' || is_array($value) || !strpos($key, '.')) {
                continue;
            }
            $response['MFOX_' . preg_replace('/\./', '_', strtoupper($key))] = $value;
        }

        return $response;
    }
}
