<?php

namespace MetaFox\Menu\Listeners;

use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;

class DeleteMenuItemListener
{
    public function handle(array $attributes): void
    {
        resolve(MenuItemRepositoryInterface::class)->deleteMenuItem($attributes);
    }
}
