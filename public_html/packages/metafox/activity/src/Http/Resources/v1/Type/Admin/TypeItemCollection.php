<?php

namespace MetaFox\Activity\Http\Resources\v1\Type\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class TypeItemCollection extends ResourceCollection
{
    public $collects = TypeItem::class;
}
