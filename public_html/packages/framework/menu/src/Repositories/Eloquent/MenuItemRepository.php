<?php

namespace MetaFox\Menu\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;
use RuntimeException;

/**
 * @method MenuItem find($id, $columns = ['*'])
 * @method MenuItem getModel()
 */
class MenuItemRepository extends AbstractRepository implements MenuItemRepositoryInterface
{
    public function model(): string
    {
        return MenuItem::class;
    }

    public function getMenuItemByMenuName(string $menuName, string $resolution, bool $isActive = null): Collection
    {
        $query = $this->getModel()
            ->newQuery()
            ->selectRaw('core_menu_items.*')
            ->join('packages', 'packages.alias', '=', 'core_menu_items.module_id')
            ->where(['menu' => $menuName, 'resolution' => $resolution])
            ->addScope(new PackageScope($this->getModel()->getTable()));

        if (isset($isActive)) {
            $query->where('core_menu_items.is_active', '=', $isActive);
        }

        $items = $query->orderBy('parent_name', 'asc')
            ->orderBy('ordering', 'asc')
            ->orderBy('label', 'asc')
            ->get();

        try {
            /*
             * @warning When adding new method arguments, also support to pass these arguments to event parameters
             */
            app('events')->dispatch('menu.menu_item.override_get_menu_item_by_menu_name', [&$items, $menuName, $resolution, $isActive]);
        } catch (\Throwable $exception) {
            Log::error('override get menu items by menu name error message: ' . $exception->getMessage());
        }

        return $items;
    }

    public function setupMenuItems(string $package, string $resolution, ?array $items): bool
    {
        if (!$items) {
            return true;
        }
        $packageId     = PackageManager::getName($package);
        $moduleId      = PackageManager::getAlias($package);
        $fields        = $this->getModel()->getFillable();
        $shouldDeletes = [];

        $inserts = [];

        foreach ($items as $item) {
            if ($item['is_deleted'] ?? false) {
                $shouldDeletes[] = [
                    'menu'        => $item['menu'] ?? '',
                    'resolution'  => $resolution,
                    'parent_name' => $item['parent_name'] ?? '',
                    'name'        => $item['name'],
                    'package_id'  => $packageId,
                ];
                continue;
            }
            $item = array_merge([
                'module_id'   => $moduleId,
                'package_id'  => $packageId,
                'menu'        => '',
                'parent_name' => null,
                'name'        => '',
                'label'       => '',
                'note'        => null,
                'ordering'    => 0,
                'is_active'   => 1,
                'resolution'  => $resolution,
                'as'          => null,
                'icon'        => null,
                'testid'      => null,
                'value'       => null,
                'to'          => null,
            ], $item);

            if ($item['parent_name'] === null) {
                $item['parent_name'] = '';
            }

            $item['extra'] = json_encode(Arr::except($item, ['is_deleted', 'version', ...$fields]));
            $inserts[]     = Arr::only($item, $fields);
        }

        // dump duplicated key.
        $keys = [];
        foreach ($inserts as $item) {
            $spice = Arr::only($item, ['menu', 'resolution', 'parent_name', 'name']);
            $key   = implode('.', $spice);
            if (in_array($key, $keys)) {
                throw new RuntimeException('duplicated menu item ' . $key);
            }
            array_push($keys, $key);
        }

        $allows = $resolution === 'admin' ? null : ['package_id', 'as', 'extra', 'value'];
        MenuItem::query()->upsert(
            $inserts,
            ['menu', 'resolution', 'parent_name', 'name'],
            $allows
        );

        if ($shouldDeletes) {
            foreach ($shouldDeletes as $where) {
                MenuItem::query()->where($where)->delete();
            }
        }

        return true;
    }

    public function loadItems(string $menuName, string $resolution): array
    {
        $return = [];

        /** @var Collection $rows */
        $rows = $this->getModel()->newQuery()
            ->select(['core_menu_items.*'])
            ->join('packages', 'packages.alias', '=', 'core_menu_items.module_id')
            ->where([
                'core_menu_items.menu'       => $menuName,
                'core_menu_items.resolution' => $resolution,
                'core_menu_items.is_active'  => 1,
                'packages.is_active'         => 1,
                'packages.is_installed'      => 1,
            ])->orderBy('parent_name')
            ->orderBy('ordering')
            ->orderBy('label')
            ->get();

        if ($rows->count()) {
            $max = (int) $rows->max('ordering') + 1;

            $rows = $rows->sortBy(function ($row) use ($max) {
                if ($row->as == 'sidebarButton') {
                    return $max + (int) $row->ordering;
                }

                return $row->ordering;
            });
        }

        try {
            /*
             * @warning When adding new method arguments, also support to pass these arguments to event parameters
             */
            app('events')->dispatch('menu.menu_item.override_load_items', [&$rows, $menuName, $resolution]);
        } catch (\Throwable $exception) {
            Log::error('override load menu items error message: ' . $exception->getMessage());
        }

        foreach ($rows as $row) {
            $data = array_trim_null(Arr::except(array_merge($row->toArray(), $row->extra), [
                // reduce response size
                'id',
                'is_active',
                'ordering',
                'version',
                'resolution',
                'menu',
                'extra',
                'created_at',
                'updated_at',
                'module_id',
                'package_id',
            ]), [
                'icon'        => '',
                'parent_name' => '',
                'as'          => '',
                'testid'      => '',
            ]);

            if ($row->label) {
                $data['label'] = __p($row->label);
            }

            if (isset($data['subInfo'])) {
                $data['subInfo'] = __p($data['subInfo']);
            }

            $return[$row->name] = $data;
        }

        // should drop null value.
        return $this->arrayToTree($return);
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<string,mixed>
     */
    public function arrayToTree(array $array): array
    {
        $grouped = [];
        foreach ($array as $node) {
            $grouped[$node['parent_name'] ?? ''][] = $node;
        }

        $fnBuilder = function ($siblings) use (&$fnBuilder, $grouped) {
            foreach ($siblings as $k => $sibling) {
                $id = $sibling['name'];
                if (isset($grouped[$id])) {
                    $items            = $fnBuilder($grouped[$id]);
                    $sibling['items'] = $items;
                }
                $siblings[$k] = $sibling;
            }

            return $siblings;
        };

        if (!isset($grouped[''])) {
            return [];
        }

        return $fnBuilder($grouped['']);
    }

    public function createMenuItem(array $params): MenuItem
    {
        $fields        = $this->getModel()->getFillable();
        $data          = Arr::only($params, $fields);
        $data['extra'] = Arr::except($params, $fields);
        $data['name']  = uniqid('menu_item_%s_');

        $menuItem = new MenuItem($data);
        $menuItem->save();

        $menuItem->updateQuietly(['name' => sprintf($menuItem->name, $menuItem->entityId())]);

        $menuItem->refresh();

        return $menuItem;
    }

    public function updateMenuItem(int $id, array $params): MenuItem
    {
        $menuItem = $this->find($id);

        if ($menuItem->resolution !== MetaFoxConstant::RESOLUTION_MOBILE) {
            if (Arr::has($params, 'iconColor')) {
                Arr::forget($params, 'iconColor');
            }
        }

        $fields        = $this->getModel()->getFillable();
        $data          = Arr::only($params, $fields);
        $extraParams   = Arr::except($params, $fields);
        $data['extra'] = is_array($menuItem->extra) ? array_merge($menuItem->extra, $extraParams) : $extraParams;

        $menuItem->update($data);

        return $menuItem->refresh();
    }

    public function dumpByPackage(string $package, string $resolution): array
    {
        $result   = [];
        $moduleId = PackageManager::getAlias($package);

        /** @var Collection<MenuItem> $allItems */
        $allItems = $this->getModel()->newQuery()
            ->where(['module_id' => $moduleId, 'resolution' => $resolution])
            ->orderBy('menu')
            ->orderBy('parent_name', 'desc')
            ->orderBy('ordering')
            ->orderBy('id')
            ->cursor();

        $excepts = [
            'id', 'created_at', 'resolution', 'menu_type', 'module_id', 'testid',
            'package_id', 'updated_at', 'extra', 'version', 'is_deleted',
        ];

        $strips = [
            'is_active'   => 1,
            'parent_name' => '',
            'version'     => 0,
        ];

        foreach ($allItems as $item) {
            $result[] = array_trim_null(
                Arr::except(array_merge($item->extra ?? [], $item->toArray()), $excepts),
                $strips
            );
        }

        return $result;
    }

    public function deleteMenuItem(array $attributes): bool
    {
        $item = $this->getModel()->newModelQuery()
            ->where($attributes)
            ->first();

        if (null === $item) {
            return true;
        }

        $item->delete();

        return true;
    }

    public function orderItems(array $orderIds): bool
    {
        $items = MenuItem::query()
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        if (!$items->count()) {
            return true;
        }

        $ordering = 1;

        foreach ($orderIds as $orderId) {
            $orderItem = $items->get($orderId);

            if (null === $orderItem) {
                continue;
            }

            $orderItem->update(['ordering' => $ordering++]);
        }

        return true;
    }

    public function getMenuItemByName(string $menu, string $name, string $resolution, ?string $parentName = null): ?MenuItem
    {
        return $this->getModel()->newQuery()
            ->where([
                'menu'        => $menu,
                'name'        => $name,
                'resolution'  => $resolution,
                'parent_name' => $parentName,
            ])
            ->first();
    }
}
