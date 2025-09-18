<?php

namespace MetaFox\Saved\Http\Resources\v1\Saved;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SavedItemCollection extends ResourceCollection
{
    protected int $listId = 0;

    public $collects = SavedItem::class;

    public function setCollectionId($listId)
    {
        $this->listId = $listId;
    }

    public function toArray($request)
    {
        return $this->collection->map(function ($item) {
            if ($this->listId) {
                $item->setCollectionId($this->listId);
            }

            return $item;
        })->toArray($request);
    }
}
