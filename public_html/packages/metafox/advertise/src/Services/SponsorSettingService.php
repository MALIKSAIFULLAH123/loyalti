<?php

namespace MetaFox\Advertise\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Advertise\Support\Support;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Authorization\Repositories\PermissionSettingRepositoryInterface;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;

class SponsorSettingService implements SponsorSettingServiceInterface
{
    /**
     * @return Collection
     */
    public function getPackageSettings(int $roleId, string $guard = 'api'): array
    {
        return Cache::rememberForever(sprintf('%s_%s', __FUNCTION__, $roleId), function () use ($roleId, $guard) {
            $activePackageIds = app('core.packages')->getActivePackageIds();

            if (!count($activePackageIds)) {
                return [];
            }

            $sponsorSettings = [];

            $sponsorSettings = $this->buildPermissions($sponsorSettings, $activePackageIds, $roleId, $guard);

            $sponsorSettings = $this->buildSettings($sponsorSettings, $activePackageIds, $roleId);

            return $sponsorSettings;
        });
    }

    protected function buildPermissions(array $sponsorSettings, array $activePackageIds, int $roleId, string $guard = 'api'): array
    {
        if (!is_array($activePackageIds) || !count($activePackageIds)) {
            return [];
        }

        $permissionBuilder = resolve(PermissionSettingRepositoryInterface::class)->getModel()->newQuery();

        $permissionTable = config('permission.table_names.permissions');

        $hasPermissionTable = config('permission.table_names.role_has_permissions');

        $permissions = $permissionBuilder
            ->leftJoin($hasPermissionTable, function (JoinClause $joinClause) use ($hasPermissionTable, $permissionTable, $roleId) {
                $joinClause->on(sprintf('%s.permission_id', $hasPermissionTable), '=', sprintf('%s.id', $permissionTable))
                    ->where(sprintf('%s.role_id', $hasPermissionTable), '=', $roleId);
            })
            ->where(sprintf('%s.guard_name', $permissionTable), '=', $guard)
            ->whereIn(sprintf('%s.action', $permissionTable), $this->getUserSettingNames())
            ->orderBy(sprintf('%s.entity_type', $permissionTable))
            ->get([sprintf('%s.*', $permissionTable), sprintf('%s.permission_id AS value', $hasPermissionTable)])
            ->map(function ($permission) {
                return [
                    'entity_type'   => $permission->entity_type,
                    'package_alias' => $permission->module_id,
                    'title'         => __p($permission->getLabelPhrase()),
                    'description'   => __p($permission->getHelpPhrase()),
                    'name'          => $permission->action,
                    'value'         => $permission->value ? 1 : 0,
                ];
            })
            ->toArray();

        if (!count($permissions)) {
            return $sponsorSettings;
        }

        foreach ($permissions as $permission) {
            if (!Arr::has($sponsorSettings, $permission['entity_type'])) {
                Arr::set($sponsorSettings, $permission['entity_type'], [
                    'title'       => __p(sprintf('%s::phrase.sponsor_setting_%s', $permission['package_alias'], $permission['entity_type'])),
                    'permissions' => [],
                ]);
            }

            Arr::set($sponsorSettings, sprintf('%s.permissions.%s', Arr::get($permission, 'entity_type'), Arr::get($permission, 'name')), [
                'title'         => Arr::get($permission, 'title'),
                'description'   => Arr::get($permission, 'description'),
                'value'         => Arr::get($permission, 'value'),
                'package_alias' => Arr::get($permission, 'package_alias'),
            ]);
        }

        return $sponsorSettings;
    }

    protected function buildSettings(array $sponsorSettings, array $activePackageIds, int $roleId, bool $buildSingle = false): array
    {
        /**
         * @var Builder $settingBuilder
         */
        $settingBuilder = SiteSetting::query();

        $table = 'core_site_settings';

        $settings = $settingBuilder
            ->join('packages', function (JoinClause $joinClause) use ($table, $activePackageIds) {
                $joinClause->on('packages.alias', '=', sprintf('%s.module_id', $table))
                    ->whereIn('packages.name', $activePackageIds);
            })
            ->where(sprintf('%s.name', 'core_site_settings'), $this->likeOperator(), '%' . sprintf('.%s', $this->getPriceSettingName()))
            ->select([sprintf('%s.*', $table), 'packages.title AS package_title', 'packages.name AS package_name'])
            ->get()
            ->map(function ($setting) use ($roleId) {
                $value = null;

                if (is_string($setting->value_actual) && MetaFoxConstant::EMPTY_STRING != $setting->value_actual) {
                    $values = json_decode($setting->value_actual, true);

                    if (is_array($values)) {
                        $value = Arr::get($values, $roleId);
                    }
                }

                $parts = explode('.', $setting->name);

                /**
                 * setting name must be module_id.entity_type.name (Etc: activity.feed.purchase_sponsor_price).
                 */
                $entityType        = count($parts) == 3 ? $parts[1] : $parts[0];
                $titlePhrase       = sprintf('%s::phrase.purchase_sponsor_price_%s_label', $setting->module_id, $entityType);
                $descriptionPhrase = sprintf('%s::phrase.purchase_sponsor_price_%s_desc', $setting->module_id, $entityType);
                $title             = __p($titlePhrase);
                $description       = __p($descriptionPhrase);

                if ($titlePhrase == $title) {
                    $title = __p(sprintf('%s::phrase.purchase_sponsor_price_label', $setting->module_id));
                }

                if ($descriptionPhrase == $description) {
                    $description = __p(sprintf('%s::phrase.purchase_sponsor_price_desc', $setting->module_id));
                }

                return [
                    'entity_type'   => $entityType,
                    'package_alias' => $setting->module_id,
                    'title'         => $title,
                    'description'   => $description,
                    'name'          => $setting->name,
                    'value'         => $value,
                ];
            })
            ->toArray();

        if (!count($settings)) {
            return $sponsorSettings;
        }

        if ($buildSingle) {
            return $settings;
        }

        foreach ($settings as $setting) {
            if (!Arr::has($sponsorSettings, $setting['entity_type'])) {
                Arr::set($sponsorSettings, $setting['entity_type'], [
                    'title'    => __p(sprintf('%s::phrase.sponsor_setting_%s', $setting['package_alias'], $setting['entity_type'])),
                    'settings' => [],
                ]);
            }

            Arr::set($sponsorSettings, sprintf('%s.settings.%s', Arr::get($setting, 'entity_type'), Str::replace('.', '_', $setting['name'])), [
                'title'       => Arr::get($setting, 'title'),
                'description' => Arr::get($setting, 'description'),
                'value'       => Arr::get($setting, 'value'),
            ]);
        }

        return $sponsorSettings;
    }

    protected function getUserSettingNames(): array
    {
        return [
            'sponsor',
            'sponsor_free',
            'sponsor_in_feed',
            'auto_publish_sponsored_item',
        ];
    }

    protected function getPriceSettingName(): string
    {
        return Support::PRICE_SETTING_NAME;
    }

    protected function likeOperator(): string
    {
        return database_driver() == 'pgsql' ? 'ilike' : 'like';
    }

    /**
     * @param User  $user
     * @param int   $roleId
     * @param array $params
     *
     * @return bool
     */
    public function updateSettings(User $user, int $roleId, array $params): bool
    {
        $role = resolve(RoleRepositoryInterface::class)->find($roleId);

        $this->updatePermissions($user, $role, Arr::get($params, 'permissions') ?? []);

        $this->updateSettingItems($user, $role, Arr::get($params, 'settings') ?? []);

        return true;
    }

    protected function updatePermissions(User $user, Role $role, array $permissions): void
    {
        if (!count($permissions)) {
            return;
        }

        resolve(PermissionRepositoryInterface::class)->updatePermissionValue($user, $role, $permissions);
    }

    protected function updateSettingItems(User $user, Role $role, array $settings): void
    {
        if (!count($settings)) {
            return;
        }

        $activePackageIds = app('core.packages')->getActivePackageIds();

        if (!count($activePackageIds)) {
            return;
        }

        $prepare = $current = [];

        $current = $this->buildSettings($current, $activePackageIds, $role->entityId(), true);

        foreach ($current as $value) {
            $var               = $value['name'];
            $formVar           = Str::replace('.', '_', $var);
            $prepare[$formVar] = [
                'var'   => $var,
                'value' => $this->getPriceValue($var),
            ];
        }

        $updates = [];

        foreach ($settings as $var => $currencies) {
            if (!isset($prepare[$var])) {
                unset($prepare[$var]);
                continue;
            }

            $roleCurrencies = [];

            if (isset($prepare[$var]['value'][$role->entityId()])) {
                $roleCurrencies = $prepare[$var]['value'][$role->entityId()];
            }

            $roleCurrencies = array_merge($roleCurrencies, $currencies);

            $roleCurrencies = \MetaFox\Advertise\Support\Facades\Support::roundPriceUp($roleCurrencies);

            $prepare[$var]['value'][$role->entityId()] = $roleCurrencies;

            Arr::set($updates, $prepare[$var]['var'], json_encode($prepare[$var]['value']));
        }

        if (!count($updates)) {
            return;
        }

        Settings::save($updates);
    }

    public function getPriceValue(string $var, ?int $roleId = null): array
    {
        $prices = Settings::get($var);

        if (null === $prices) {
            return [];
        }

        if (is_string($prices)) {
            $prices = json_decode($prices, true);
        }

        if (!is_array($prices)) {
            return [];
        }

        $prices = \MetaFox\Advertise\Support\Facades\Support::roundPriceUp($prices);

        if (null === $roleId) {
            return $prices;
        }

        $rolePrices = Arr::get($prices, $roleId);

        if (!is_array($rolePrices)) {
            return [];
        }

        return $rolePrices;
    }

    public function getPriceForPayment(User $user, Content $resource, ?string $currencyId = null): ?float
    {
        $userRole = resolve(RoleRepositoryInterface::class)->roleOf($user);

        $moduleId = getAliasByEntityType($resource->entityType());

        if (!$moduleId) {
            return null;
        }

        $prices = $this->getPriceValue(sprintf('%s.%s.%s', $moduleId, $resource->entityType(), $this->getPriceSettingName()), $userRole->entityId());

        if (!is_array($prices)) {
            return null;
        }

        if (!count($prices)) {
            return null;
        }

        if (null === $currencyId) {
            $currencyId = app('currency')->getUserCurrencyId($user);
        }

        $price = Arr::get($prices, $currencyId);

        if (!is_numeric($price)) {
            return null;
        }

        if ($price < 0) {
            return null;
        }

        return $price;
    }
}
