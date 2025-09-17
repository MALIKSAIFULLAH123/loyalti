<?php

namespace MetaFox\Menu\Repositories;

use MetaFox\Menu\Models\Menu;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Menu.
 * @method Menu find($id, $columns = ['*'])
 * @method Menu getModel()
 * @mixin BaseRepository
 */
interface MenuRepositoryInterface
{
    /**
     * @param array<string,mixed> $menus
     */
    public function setupMenus(string $package, ?array $menus): void;

    /**
     * @param  string              $menuName
     * @param  string              $resolution
     * @return array<string,mixed>
     */
    public function loadMenuByName(string $menuName, string $resolution): array;

    /**
     * Load all menus.
     * @param  string $resolution
     * @param  bool   $isResource
     * @return array
     */
    public function loadMenus(string $resolution, bool $isResource): array;

    /**
     * @return array<string,string>
     */
    public function getMenuOptions(): array;

    /**
     * @return array<string,string>
     */
    public function getMenuNameOptions(): array;

    /**
     * This method is help to export menu from database to local resource/menu.php.
     *
     * @param string $packageName
     *
     * @return array<mixed>
     */
    public function getByPackage(string $packageName): array;

    /**
     * @param  string $menuName
     * @param  string $resolution
     * @return bool
     */
    public function isExists(string $menuName, string $resolution): bool;
}
