<?php

namespace MetaFox\User\Http\Resources\v1\UserGender\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\UserGender as Model;

/**
 * Class UserGenderItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserGenderItem extends JsonResource
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
            'id'                => $this->resource->id,
            'resource_name'     => $this->resource->entityType(),
            'module_name'       => 'user',
            'phrase'            => $this->resource->phrase,
            'name'              => $this->resource->name,
            'is_custom'         => $this->resource->is_custom,
            'is_system'         => $this->resource->is_system,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
