<?php

namespace MetaFox\Search\Http\Resources\v1\Search;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SearchGroupItem extends JsonResource
{
    public function toArray($request): array
    {
        $itemType = Arr::get($this->resource, 'item_type');

        return [
            'module_name'        => 'search',
            'resource_name'      => 'search_group',
            'id'                 => Str::uuid(),
            'item_module_name'   => Arr::get($this->resource, 'item_module_name'),
            'item_resource_name' => $itemType,
            'label'              => Arr::get($this->resource, 'label'),
            'item_type'          => $itemType,
            'total_item'         => Arr::get($this->resource, 'total_item', 0),
        ];
    }
}
