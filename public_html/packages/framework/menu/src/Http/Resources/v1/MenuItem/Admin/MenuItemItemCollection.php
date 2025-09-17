<?php

namespace MetaFox\Menu\Http\Resources\v1\MenuItem\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class MenuItemItemCollection extends ResourceCollection
{
    public $collects = MenuItemItem::class;
}
