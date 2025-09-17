<?php

use Illuminate\Support\Facades\Route;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Platform\Facades\Settings;

Route::get('search/{filterType}', function ($filterType) {
    return seo_sharing_view(
        'admin',
        'search.search.search_landing_by_type',
        null,
        null,
        function ($meta, $resource) use ($filterType) {
            $type = str_replace('-', '_', $filterType);
            $menu = resolve(MenuItemRepositoryInterface::class)
            ->getMenuItemByName('search.webCategoryMenu', $type, 'web', '');

            if ($menu instanceof MenuItem) {
                $title = $meta->offsetGet('title');

                $meta->offsetSet('title', sprintf('%s - %s', __p($menu->label), $title));
            }
        }
    );
});
