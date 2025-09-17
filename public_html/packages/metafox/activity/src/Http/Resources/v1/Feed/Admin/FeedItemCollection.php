<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class FeedItemCollection extends ResourceCollection
{
    public $collects = FeedItem::class;

    public function toArray(Request $request)
    {
        return $this->collection->map(function ($item) use ($request) {
            if (!$item instanceof JsonResource) {
                return [];
            }

            try {
                return $item->toArray($request);
            } catch (\Throwable $error) {
                logger()->error($error->getMessage());
                logger()->error($error->getTraceAsString());
            }

            return [];
        })
        ->filter(function ($item) {
            return !empty($item);
        })->all();
    }
}
