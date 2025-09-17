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
class UserRelationDetail extends JsonResource
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
            'module_name'   => 'user',
            'resource_name' => $this->resource->entityType(),
            'phrase_var'    => $this->resource->phrase_var,
            'confirm'       => $this->resource->entityType(),
        ];
    }
}
