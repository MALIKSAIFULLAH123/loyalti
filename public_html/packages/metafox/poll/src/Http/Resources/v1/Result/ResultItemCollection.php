<?php

namespace MetaFox\Poll\Http\Resources\v1\Result;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use MetaFox\User\Models\User;

class ResultItemCollection extends ResourceCollection
{
    public $collects = ResultItem::class;

    public function toArray($request)
    {
        $this->collection = $this->collection->filter(function ($item) {
            return $item->user instanceof User;
        });

        return parent::toArray($request);
    }
}
