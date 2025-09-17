<?php

namespace MetaFox\Menu\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Menu\Models\MenuItem;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface MenuItem.
 * @method MenuItem find($id, $columns = ['*'])
 * @method MenuItem getModel()
 * @mixin BaseRepository
 */
interface MenuItemRepositoryInterface
{
    /**
     * @param string $menuName
     * @param string $resolution
     * @param ?bool  $isActive
     *
     * @return Collection
     */
    public function getMenuItemByMenuName(string $menuName, string $resolution, bool $isActive = null): Collection;

    /**
     * @param  string       $menuName
     * @param  string       $resolution
     * @return array<mixed>
     */
    public function loadItems(string $menuName, string $resolution): array;

    /**
     * @param string            $package
     * @param string            $resolution
     * @param array<mixed>|null $items
     */
    public function setupMenuItems(string $package, string $resolution, ?array $items): bool;

    /**
     * @param array<string, mixed> $params
     *
     * @return MenuItem
     */
    public function createMenuItem(array $params): MenuItem;

    /**
     * @param int                  $id
     * @param array<string, mixed> $params
     *
     * @return MenuItem
     */
    public function updateMenuItem(int $id, array $params): MenuItem;

    /**
     * Export all menu items from the database to ./resources/menu/items.php.
     *
     * @param  string       $package
     * @param  string       $resolution
     * @return array<mixed>
     */
    public function dumpByPackage(string $package, string $resolution): array;

    /**
     * @param  array<mixed> $attributes
     * @return bool
     */
    public function deleteMenuItem(array $attributes): bool;

    /**
     * @param  array<int> $orderIds
     * @return bool
     */
    public function orderItems(array $orderIds): bool;

    /**
     * @param  string        $menu
     * @param  string        $name
     * @param  string        $resolution
     * @param  string|null   $parentName
     * @return MenuItem|null
     */
    public function getMenuItemByName(string $menu, string $name, string $resolution, ?string $parentName = null): ?MenuItem;
}
