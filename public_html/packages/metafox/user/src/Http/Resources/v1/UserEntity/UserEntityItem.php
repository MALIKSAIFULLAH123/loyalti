<?php

namespace MetaFox\User\Http\Resources\v1\UserEntity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\UserEntity as Model;

/**
 * Class UserEntityItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserEntityItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->id,
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'full_name'     => $this->resource->name,
            'display_name'  => $this->resource->display_name,
            'user_name'     => $this->resource->user_name,
            'avatar'        => $this->resource->avatar,
            'is_featured'   => $this->resource->is_featured,
            'short_name'    => $this->resource->short_name,
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'router'        => $this->resource->toRouter(),
        ];
    }
}
