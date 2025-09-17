<?php

namespace MetaFox\Authorization\Http\Resources\v1\Device;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Authorization\Models\UserDevice as Model;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class DeviceDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class DeviceDetail extends JsonResource
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
            'id'                => $this->resource->entityId(),
            'module_name'       => 'firebase',
            'resource_name'     => $this->resource->entityType(),
            'device_id'         => $this->resource->device_id,
            'device_uid'        => $this->resource->device_uid,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
