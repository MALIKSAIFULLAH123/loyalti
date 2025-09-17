<?php

namespace MetaFox\Storage\Http\Resources\v1\Disk\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class DiskItemCollection extends ResourceCollection
{
    public $collects = DiskItem::class;
}
