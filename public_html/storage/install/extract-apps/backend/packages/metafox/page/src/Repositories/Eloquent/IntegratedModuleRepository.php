<?php

namespace MetaFox\Page\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Page\Models\IntegratedModule;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
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
     * @return PageRepositoryInterface
     */
    private function pageRepository(): PageRepositoryInterface
    {
        return resolve(PageRepositoryInterface::class);
    }

    protected function menuItemRepository(): MenuItemRepositoryInterface
    {
        return resolve(MenuItemRepositoryInterface::class);
    }

    public function addModules(int $pageId): void
    {
        $menuItems = $this->menuItemRepository()
            ->getMenuItemByMenuName(IntegratedModule::MENU_NAME, 'web');
        $menus     = [];

        foreach ($menuItems as $item) {
            if (!app_active($item['package_id'])) {
                continue;
            }

            $extra   = $item['extra'];
            $menus[] = [
                'page_id'    => $pageId,
                'name'       => $item['name'],
                'label'      => $item['label_var'],
                'is_active'  => $item['is_active'],
                'ordering'   => $item['ordering'],
                'module_id'  => $item['module_id'],
                'package_id' => $item['package_id'],
                'tab'        => Arr::get($extra, 'tab'),
            ];
        }

        $this->getModel()->newQuery()->insertOrIgnore($menus);
    }

    /**
     * @inheritDoc
     */
    public function getModules(int $pageId): Collection
    {
        $query = $this->getModel()->newQuery()
            ->where('page_id', $pageId);
        $table = $this->getModel()->getTable();

        if (!$query->exists()) {
            $this->addModules($pageId);
        }

        $this->handleNewModule($pageId);

        $query->addScope(resolve(PackageScope::class, [
            'table' => $table,
        ]));

        return $query->orderBy("{$table}.ordering")
            ->get();
    }

    /**
     * @param int $pageId
     *
     * @return void
     */
    protected function handleNewModule(int $pageId): void
    {
        $menuItems = $this->menuItemRepository()
            ->getMenuItemByMenuName(IntegratedModule::MENU_NAME, 'web');
        $menuGroup = $this->getModel()->newQuery()
            ->where('page_id', $pageId)
            ->pluck('name')
            ->toArray();

        if (count($menuGroup) == $menuItems->count()) {
            return;
        }

        foreach ($menuItems as $item) {
            if (in_array($item['name'], $menuGroup)) {
                continue;
            }

            $menus = [
                'page_id'    => $pageId,
                'name'       => $item['name'],
                'label'      => $item['label_var'],
                'is_active'  => (int) in_array($item['name'], IntegratedModule::TAB_NAME_DEFAULTS),
                'ordering'   => $item['ordering'],
                'module_id'  => $item['module_id'],
                'package_id' => $item['package_id'],
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
        $pageId = Arr::get($attributes, 'page_id', 0);
        $page   = $this->pageRepository()->find($pageId);

        policy_authorize(PagePolicy::class, 'manageMenuSetting', $context, $page);

        $names = Arr::get($attributes, 'names', []);

        if ($pageId <= 0) {
            return false;
        }

        foreach ($names as $key => $name) {
            $query = $this->getModel()->newQuery()
                ->where('page_id', $pageId)
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
    public function updateModule(User $context, int $pageId, array $params): bool
    {
        $page = $this->pageRepository()->find($pageId);
        policy_authorize(PagePolicy::class, 'manageMenuSetting', $context, $page);

        foreach ($params as $name => $value) {
            $this->getModel()->newQuery()
                ->where('page_id', $pageId)
                ->where('name', $name)
                ->update(['is_active' => $value]);
        }

        return true;
    }

    /**
     * @param int $pageId
     *
     * @return array
     */
    public function getProfileMenuSettings(int $pageId): array
    {
        $modules      = $this->getModules($pageId);
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

        $menus = $this->getMenuItemOfPageByMenu()
            ->where('resolution', MetaFoxConstant::RESOLUTION_MOBILE)
            ->where('module_id', $menu->module_id)
            ->pluck('module_id', 'name')
            ->toArray();

        if (!$menus) {
            return $menuSettings;
        }

        foreach ($menus as $name => $key) {
            $menuSettings[$name] = (bool) $menu->is_active;
        }

        return $menuSettings;
    }

    protected function getMenuItemOfPageByMenu(): Collection
    {
        return Cache::rememberForever('getMenuItemOfPageByMenu', function () {
            return $this->menuItemRepository()->getModel()->newQuery()
                ->where('menu', IntegratedModule::MENU_NAME)
                ->orderBy('ordering')
                ->get();
        });
    }

    public function getMenusByPage(int $pageId): array
    {
        return Cache::rememberForever('getMenusByPage' . "($pageId)", function () use ($pageId) {
            return $this->getModel()->newQuery()
                ->where('page_id', $pageId)
                ->pluck('name', 'tab')
                ->toArray();
        });
    }
}
