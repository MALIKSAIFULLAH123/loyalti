<?php

namespace MetaFox\Search\Http\Resources\v1\Search;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class TrendingHashtagItem extends JsonResource
{
    public function toArray($request)
    {
        $hashtag = Str::after($this->resource->text, '#');
        $hashtag = '#' . $hashtag;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'search',
            'resource_name' => $this->resource->entityType(),
            'text'          => $hashtag,
            'tag_url'       => $this->resource->tag_url,
            'name'          => $this->resource->tag_url,
            'link'          => $this->resource->toLink(),
            'statistic'     => [
                'total_item' => $this->resource->total_item,
            ],
        ];
    }
}
