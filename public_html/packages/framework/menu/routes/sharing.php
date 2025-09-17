<?php

use Illuminate\Support\Facades\Route;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;
use Illuminate\Support\Str;
use MetaFox\Menu\Models\Menu;

Route::get('menu/menu/{id}/menu-item/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.menu.browse_menu_item',
        'menu',
        $id,
        function ($data, $menu) use ($id) {
            $label  = $menu?->title;
            if (!$label && $menu) {
                $label = $menu?->name;
            }

            if (!$label) {
                $label = 'Menu #' . $id;
            }
            $data->addBreadcrumb($label, null);
        }
    );
});

Route::get('menu/menu_item/{id}/child/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.menu.browse_menu_item_children',
        'menuitem',
        $id,
        function ($data, $menuItem) use ($id) {
            $parentMenu = resolve(MenuRepositoryInterface::class)
                ->getModel()
                ->newModelQuery()
                ->where('name', $menuItem->menu)
                ->where('resolution', 'admin')
                ->first();

            $menuLink  = null;
            $menuLabel = Str::headline($menuItem->parent_name);

            if ($parentMenu instanceof Menu) {
                $menuLabel = __p($parentMenu->title);
                $menuLink  = "menu/menu/{$parentMenu->entityId()}/menu-item/browse";
            }
            $data->addBreadcrumb($menuLabel, $menuLink);
            $data->addBreadcrumb(__p($menuItem->label), null);
        }
    );
});
