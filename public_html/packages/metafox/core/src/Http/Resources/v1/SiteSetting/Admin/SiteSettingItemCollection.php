<?php

namespace MetaFox\Core\Http\Resources\v1\SiteSetting\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SiteSettingItemCollection extends ResourceCollection
{
    public $collects = SiteSettingItem::class;
}
