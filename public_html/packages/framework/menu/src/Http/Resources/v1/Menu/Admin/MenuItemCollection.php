<?php

namespace MetaFox\Menu\Http\Resources\v1\Menu\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class MenuItemCollection extends ResourceCollection
{
    public $collects = MenuItem::class;
}
