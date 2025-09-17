<?php

namespace MetaFox\Broadcast\Http\Resources\v1\Connection\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Models\Driver as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class DriverItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ConnectionItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'title' => __p($this->resource->title),
            'links'=> [
                'editUrl'=> $this->resource->url
            ]
        ];
    }
}
