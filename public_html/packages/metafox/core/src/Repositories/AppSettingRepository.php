<?php

namespace MetaFox\Core\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Models\Role;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\Contracts\AppSettingRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Resource\Actions;

class AppSettingRepository implements AppSettingRepositoryInterface
{
    const APP_SETTING_TYPE_FORMS = 'forms';
    const APP_SETTING_TYPE_ACTIONS = 'actions';
    const APP_SETTING_TYPE_APP_MENUS = 'appMenus';
    const APP_SETTING_TYPE_RESOURCE_MENUS = 'resourceMenus';
    const APP_SETTING_TYPE_ACL = 'acl';
    const APP_SETTING_TYPE_ASSETS = 'assets';
    const APP_SETTING_TYPE_SETTINGS = 'settings';
    const APP_SETTING_ALLOWABLE_TYPES = [
        self::APP_SETTING_TYPE_FORMS,
        self::APP_SETTING_TYPE_ACTIONS,
        self::APP_SETTING_TYPE_APP_MENUS,
        self::APP_SETTING_TYPE_RESOURCE_MENUS,
        self::APP_SETTING_TYPE_ACL,
        self::APP_SETTING_TYPE_ASSETS,
        self::APP_SETTING_TYPE_SETTINGS,
    ];


    public function getMobileSettings(Request $request, Role $role): array
    {
        return $this->getSettings($role, 'mobile', $request->get('type'));
    }

    public function getAdminSettings(Request $request, Role $role): array
    {
        return $this->getSettings($role, 'admin', $request->get('type'));
    }

    public function getWebSettings(Request $request, Role $role): array
    {
        return $this->getSettings($role, 'web', $request->get('type'));
    }

    public function getSettingsByType(Role $role, string $type, string $resolution): array
    {
        return $this->getSettings($role, $resolution, $type);
    }

    /**
     * @param  string            $for web, mobile, admin
     * @return array<int, mixed>
     */
    public function loadActions(string $for): array
    {
        $results = [];

        $type = match ($for) {
            'mobile' => Constants::DRIVER_TYPE_RESOURCE_ACTIONS,
            default  => Constants::DRIVER_TYPE_RESOURCE_WEB,
        };

        $drivers = resolve(DriverRepositoryInterface::class)
            ->loadDrivers($type, $for);

        foreach ($drivers as $driver) {
            [$resourceName, $class, , $appName, $packageId, $alt] = $driver;

            if (!class_exists($class)) {
                continue;
            }
            $setting = new $class($appName, $resourceName);

            if (!$setting instanceof Actions) {
                continue;
            }

            $alias = PackageManager::getAliasFor($packageId, $for);

            $data = $setting->toArray();

            Arr::set($results, sprintf('%s.%s', $alias, $resourceName), $data);

            if ($alt) {
                Arr::set($results, sprintf('%s.%s', $alias, $alt), $data);
            }

            if (!in_array($for, ['mobile', 'admin'])) {
                continue;
            }

            /**
             * We need to backup for mobile old version when app changed alias, so make sure old mobile version still working with new API version
             * Beside that AdminCP should use origin instead of alias, so we should return both alias and origin.
             */

            $originalAlias = PackageManager::getAliasFor($packageId);

            if ($alias == $originalAlias) {
                continue;
            }

            Arr::set($results, sprintf('%s.%s', $originalAlias, $resourceName), $data);

            if ($alt) {
                Arr::set($results, sprintf('%s.%s', $originalAlias, $alt), $data);
            }
        }

        return $results;
    }

    /**
     * @param  string               $for
     * @return array<string, mixed>
     */
    public function loadForms(string $for): array
    {
        $results = [];

        $drivers = resolve(DriverRepositoryInterface::class)
            ->loadDrivers(Constants::DRIVER_TYPE_FORM, $for, true, null, true);

        foreach ($drivers as $driver) {
            try {
                [$name, $class, , , $packageId] = $driver;

                if (!class_exists($class)) {
                    continue;
                }

                /** @var AbstractForm $setting */
                $setting = new $class($name);

                $data  = $setting->toArray(request());

                $alias = PackageManager::getAliasFor($packageId, $for);

                Arr::set($results, "$alias.$name", $data);

                if ($for != 'mobile') {
                    continue;
                }

                /**
                 * We need to backup for mobile old version when app changed alias, so make sure old mobile version still working with new API version
                 */

                $originalAlias = PackageManager::getAliasFor($packageId);

                if ($alias == $originalAlias) {
                    continue;
                }

                Arr::set($results, "$originalAlias.$name", $data);
            } catch (\Exception $exception) {}
        }

        return $results;
    }

    public function loadMorphMap()
    {
    }

    public function getSettings(Role $role, string $resolution, ?string $key = null)
    {
        if (!empty($key)) {
            return [
                $key => $this->getByResolutionAndKey($role, $resolution, $key),
            ];
        }

        return $this->getAllByResolution($role, $resolution);
    }

    private function getAllByResolution(Role $role, string $resolution)
    {
        $settings = [];
        foreach (self::APP_SETTING_ALLOWABLE_TYPES as $key) {
            $settings[$key] = $this->getByResolutionAndKey($role, $resolution, $key);
        }

        return $settings;
    }

    private function getByResolutionAndKey(Role $role, string $resolution, ?string $key = null): array
    {
        switch ($key) {
            case self::APP_SETTING_TYPE_FORMS:
                return $this->loadForms($resolution);
            case self::APP_SETTING_TYPE_ACTIONS:
                return $this->loadActions($resolution);
            case self::APP_SETTING_TYPE_APP_MENUS:
                return resolve('menu')->loadMenus($resolution, false);
            case self::APP_SETTING_TYPE_RESOURCE_MENUS:
                return resolve('menu')->loadMenus($resolution, true);
            case self::APP_SETTING_TYPE_ACL:
                return app('perms')->getPermissions($role);
            case self::APP_SETTING_TYPE_ASSETS:
                return app('asset')->loadAssetSettings();
            case self::APP_SETTING_TYPE_SETTINGS:
                return Settings::getSiteSettings($resolution, true);
            default:
                return [];
        }
    }
}
