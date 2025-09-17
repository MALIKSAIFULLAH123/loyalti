<?php

namespace MetaFox\Authorization\Http\Resources\v1\Permission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Authorization\Models\Permission as Model;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
|
*/

/**
 * Class PermissionDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PermissionDetail extends JsonResource
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
            'id'                => $this->resource->entityId(),
            'name'              => $this->resource->name,
            'description'       => $this->resource->description,
            'guard_name'        => $this->resource->guard_name,
            'resource_name'     => $this->resource->entityType(),
            'created_date'      => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
