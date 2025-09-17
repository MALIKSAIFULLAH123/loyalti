<?php

namespace MetaFox\Authorization\Http\Resources\v1\Role\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Authorization\Models\Role as Model;

/**
 * Class RoleItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class RoleDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'              => $this->resource->id,
            'module_name'     => 'user',
            'name'            => $this->resource->name,
            'resource_name'   => $this->resource->entityType(),
            'is_special'      => $this->resource->is_special,
            'is_custom'       => $this->resource->is_custom,
            'total_users'     => $this->resource?->users_count,
            'total_inherited' => $this->resource->total_inherited,
            'url'             => $this->resource->permission_link,
        ];
    }
}
