<?php

namespace MetaFox\User\Http\Resources\v1\UserRelation\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\UserRelation as Model;

/**
 * Class UserRelationItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserRelationItem extends JsonResource
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
            'id'            => $this->resource->entityId(),
            'module_name'   => '',
            'resource_name' => $this->resource->entityType(),
            'is_custom'     => (bool) $this->resource->is_custom,
            'is_active'     => $this->resource->is_active,
            'phrase_var'    => $this->resource->phrase_var,
            'title'         => __p($this->resource->phrase_var),
            'avatar'        => $this->resource->avatar,
            'links'         => [
                'editItem' => $this->resource->admin_edit_url,
            ],
        ];
    }
}
