<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedList;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SavedListDataItemCollection extends ResourceCollection
{
    public $collects = SavedListDataItem::class;
}
