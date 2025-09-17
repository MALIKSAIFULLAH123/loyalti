<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use MetaFox\Group\Models\IntegratedModule;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Group\Support\Support;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class IntegratedModuleRepository.
 */
class IntegratedModuleRepository extends AbstractRepository implements IntegratedModuleRepositoryInterface
{
    public function model()
    {
        return IntegratedModule::class;
    }

    /**
     * @return GroupRepositoryInterface
     */
    private function groupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }

    protected function menuItemRepository(): MenuItemRepositoryInterface
    {
        return resolve(MenuItemRepositoryInterface::class);
    }

    /**
     * @param int $groupId
     *
     * @return Builder
     */
    protected function initializeBuilder(int $groupId): Builder
    {
        $builder = $this->getModel()->newQuery()
            ->where('group_integrated_modules.group_id', $groupId);

        try {
            app('events')->dispatch('group.integrated_module.hook_on_builder', [$builder, $groupId]);
        } catch (\Throwable $exception) {
            Log::error('override integrated module group builder error: ' . $exception->getMessage());
            Log::error('override integrated module group builder error trace: ' . $exception->getTraceAsString());
        }

        return $builder;
    }

    public function addModules(int $groupId): void
    {
        $menuItems = $this->menuItemRepository()->getMenuItemByMenuName(IntegratedModule::MENU_NAME, 'web');
        $menus     = [];

        foreach ($menuItems as $item) {
            if (!app_active($item['package_id'])) {
                continue;
            }
            $extra   = $item['extra'];
            $menus[] = [
                'group_id'   => $groupId,
                'name'       => $item['name'],
                'label'      => $item['label_var'],
                'is_active'  => $item['is_active'],
                'ordering'   => $item['ordering'],
                'module_id'  => $item['module_id'],
                'package_id' => $item['package_id'],
                'tab'        => Arr::get($extra, 'tab', $item['name']),
            ];
        }

        $this->getModel()->newQuery()->insertOrIgnore($menus);
    }

    /**
     * @inheritDoc
     */
    public function getModules(int $groupId): Collection
    {
        $query = $this->initializeBuilder($groupId);

        if (!$query->exists()) {
            $this->addModules($groupId);
        }

        $this->handleNewModule($groupId);

        $query->addScope(resolve(PackageScope::class, [
            'table' => $this->getModel()->getTable(),
        ]));

        return $query->orderBy("{$this->getModel()->getTable()}.ordering")
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getModulesActive(int $groupId): Collection
    {
        $query = $this->initializeBuilder($groupId)
            ->where('is_active', MetaFoxConstant::IS_ACTIVE);

        if (!$query->exists()) {
            $this->addModules($groupId);
        }

        $table = $this->getModel()->getTable();
        $query->addScope(resolve(PackageScope::class, [
            'table' => $table,
        ]));

        return $query->orderBy("{$table}.ordering")
            ->get();
    }

    protected function handleNewModule(int $groupId): void
    {
        $menuItems = $this->menuItemRepository()->getMenuItemByMenuName(IntegratedModule::MENU_NAME, 'web');

        /**
         * Do not use initializeBuilder method because in this case we are checking and creating missing menus.
         */
        $menuGroup = $this->getModel()->newQuery()
            ->where('group_id', $groupId)
            ->pluck('name')
            ->toArray();

        if (count($menuGroup) == $menuItems->count()) {
            return;
        }

        foreach ($menuItems as $item) {
            if (in_array($item['name'], $menuGroup)) {
                continue;
            }

            $extra = $item['extra'];
            $menus = [
                'group_id'   => $groupId,
                'name'       => $item['name'],
                'label'      => $item['label_var'],
                'is_active'  => (int) in_array($item['name'], Support::TAB_NAME_DEFAULTS),
                'ordering'   => $item['ordering'],
                'module_id'  => $item['module_id'],
                'package_id' => $item['package_id'],
                'tab'        => Arr::get($extra, 'tab', $item['name']),
            ];

            $this->getModel()->newQuery()->insertOrIgnore($menus);
        }
    }

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function orderModules(User $context, array $attributes): bool
    {
        $groupId = Arr::get($attributes, 'group_id', 0);
        $group   = $this->groupRepository()->find($groupId);

        policy_authorize(GroupPolicy::class, 'manageMenuSetting', $context, $group);

        $names = Arr::get($attributes, 'names', []);

        if ($groupId <= 0) {
            return false;
        }

        foreach ($names as $key => $name) {
            $query = $this->initializeBuilder($groupId)
                ->where('name', $name);

            if (!$query->exists()) {
                return false;
            }

            $query->update(['ordering' => $key]);
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function updateModule(User $context, int $groupId, array $params): bool
    {
        $group = $this->groupRepository()->find($groupId);
        policy_authorize(GroupPolicy::class, 'manageMenuSetting', $context, $group);

        foreach ($params as $name => $value) {
            $this->initializeBuilder($groupId)
                ->where('name', $name)
                ->update(['is_active' => $value]);
        }

        return true;
    }

    public function getProfileMenuSettings(int $groupId): array
    {
        $modules      = $this->getModules($groupId);
        $menuSettings = [];

        foreach ($modules as $menu) {
            if (!$menu instanceof IntegratedModule) {
                continue;
            }

            $menuSettings[$menu->name] = (bool) $menu->is_active;
            $menuSettings              = $this->handleProfileMenuMobile($menuSettings, $menu);
        }

        return $menuSettings;
    }

    protected function handleProfileMenuMobile(array $menuSettings, IntegratedModule $menu): array
    {
        if (!MetaFox::isMobile()) {
            return $menuSettings;
        }

        $menus = $this->getMenuItemOfGroupByMenu()
            ->where('resolution', MetaFoxConstant::RESOLUTION_MOBILE)
            ->where('module_id', $menu->module_id)
            ->pluck('is_active', 'name')
            ->toArray();

        if (!$menus) {
            return $menuSettings;
        }

        foreach ($menus as $name => $value) {
            if (!Arr::has($menuSettings, $name)) {
                $menuSettings[$name] = (bool) $menu->is_active;
            }
        }

        return $menuSettings;
    }

    protected function getMenuItemOfGroupByMenu(): Collection
    {
        return Cache::rememberForever('getMenuItemOfGroupByMenu', function () {
            return $this->menuItemRepository()->getModel()->newQuery()
                ->where('menu', IntegratedModule::MENU_NAME)
                ->orderBy('ordering')
                ->get();
        });
    }
}
