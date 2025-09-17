<?php
namespace MetaFox\Forum\Http\Resources\v1\Moderator\Admin;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ModeratorItemCollection extends ResourceCollection
{
    public $collects = ModeratorItem::class;
}
