<?php

namespace MetaFox\Menu\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Support\CacheManager;
use MetaFox\Menu\Models\Menu;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * @method Menu find($id, $columns = ['*'])
 * @method Menu getModel()
 */
class MenuRepository extends AbstractRepository implements MenuRepositoryInterface
{
    public function model(): string
    {
        return Menu::class;
    }

    public function setupMenus(string $package, ?array $menus): void
    {
        if (!$menus) {
            return;
        }

        $fields = $this->getModel()->getFillable();

        $moduleId      = PackageManager::getAlias($package);
        $inserts       = [];
        $deletedMenus  = [];

        foreach ($menus as $data) {
            if (!$data['name']) {
                continue;
            }

            if ($data['is_deleted'] ?? false) {
                $deletedMenus[] = [
                    'name'       => $data['name'],
                    'module_id'  => $moduleId,
                    'package_id' => $package,
                    'resolution' => $data['resolution'] ?? 'web',
                ];
                continue;
            }

            $data = array_merge([
                'module_id'     => $moduleId,
                'package_id'    => $package,
                'resolution'    => $data['menu_type'] ?? 'web',
                'name'          => '',
                'type'          => 'context',
                'title'         => '',
                'description'   => '',
                'is_active'     => 1,
                'resource_name' => null,
            ], $data);

            $data['extra'] = json_encode(Arr::except($data, $fields));

            $inserts[] = Arr::only($data, $fields);
        }

        // dump duplicated key.
        $keys = [];
        foreach ($inserts as $item) {
            $key = implode('.', Arr::only($item, ['name', 'resolution']));
            if (in_array($key, $keys)) {
                throw new \RuntimeException('duplicated menu ' . $key);
            }
            array_push($keys, $key);
        }

        Menu::query()->upsert(
            $inserts,
            ['name', 'resolution'],
            ['package_id', 'name', 'type', 'title', 'description']
        );

        if (count($deletedMenus)) {
            foreach ($deletedMenus as $wheres) {
                Menu::query()->where($wheres)->delete();
            }
        }
    }

    public function loadMenuByName(string $menuName, string $resolution): array
    {
        /** @var array<string, mixed> */
        $result = [];

        $itemRepository  = resolve(MenuItemRepositoryInterface::class);
        $result['items'] = $itemRepository->loadItems($menuName, $resolution);
        $result['name']  = $menuName;

        return $result;
    }

    public function loadMenus(string $resolution, bool $isResource): array
    {
        /** @var array<string, mixed> */
        $return = [];

        $query = $this->getModel()
            ->newQuery()
            ->select(['core_menus.*'])
            ->join('packages', 'packages.alias', '=', 'core_menus.module_id')
            ->where([
                'resolution'            => $resolution,
                'packages.is_active'    => 1,
                'packages.is_installed' => 1,
            ]);

        if ($isResource) {
            $query = $query->whereNotNull('resource_name');
        } else {
            $query = $query->whereNull('resource_name');
        }

        /** @var string[] */
        $menuNames = $query->pluck('module_id', 'name');

        foreach ($menuNames as $menuName => $moduleName) {
            Arr::set($return, "$menuName", $this->loadMenuByName($menuName, $resolution));
        }

        return $return;
    }

    public function getMenuOptions(): array
    {
        return Cache::remember(CacheManager::CORE_MENU_GET_OPTIONS, CacheManager::CORE_MENU_CACHE_TIME, function () {
            $return = [];
            $menus  = $this->all();

            foreach ($menus as $menu) {
                $return[] = ['value' => $menu->id, 'label' => $menu->name];
            }

            return $return;
        });
    }

    public function getMenuNameOptions(): array
    {
        return Cache::remember(CacheManager::CORE_MENU_GET_OPTIONS, CacheManager::CORE_MENU_CACHE_TIME, function () {
            $return = [];
            $menus  = $this->getModel()->newModelQuery()->whereIn('type', ['site', 'sidebar', 'profile'])->get();

            foreach ($menus as $menu) {
                $return[] = ['value' => $menu->name, 'label' => $menu->name];
            }

            return $return;
        });
    }

    public function getByPackage(string $packageName): array
    {
        /** @var array<string, mixed> */
        $result = [];

        $moduleId = PackageManager::getAlias($packageName);
        $wheres   = ['module_id' => $moduleId];

        /** @var Menu[] $allItems */
        $allItems = $this->findWhere($wheres)->all();

        $defaults = ['resource_name' => '', 'is_active' => 1, 'version' => 1, 'filename' => '', 'description' => ''];

        foreach ($allItems as $item) {
            $arr = $item->toArray();
            Arr::forget(
                $arr,
                ['id', 'created_at', 'updated_at', 'filename', 'module_id', 'package_id', 'is_mobile', 'is_admin']
            );
            $result[] = array_trim_null($arr, $defaults);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function isExists(string $menuName, string $resolution): bool
    {
        return $this->getModel()
            ->newQuery()
            ->where([
                'name'       => $menuName,
                'resolution' => $resolution,
            ])->exists();
    }
}
