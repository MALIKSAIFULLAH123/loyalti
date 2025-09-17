<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Http\Resources\v1\User\UserItem;

/**
 * Class TagItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TagItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->id,
            'tag'          => $this->resource->tag,
        ];
    }
}
