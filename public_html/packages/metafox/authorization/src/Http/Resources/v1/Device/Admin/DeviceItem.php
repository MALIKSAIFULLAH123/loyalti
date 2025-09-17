<?php

namespace MetaFox\Authorization\Http\Resources\v1\Device\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Authorization\Models\UserDevice as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class DeviceItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class DeviceItem extends JsonResource
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
            'id'              => $this->resource->entityId(),
            'resource_name'   => $this->resource->entityType(),
            'module'          => 'firebase',
            'platform'        => $this->resource->platform,
            'device_id'       => $this->resource->device_id,
            'device_uid'      => $this->resource->device_uid,
            'creation_date'   => $this->resource->created_at,
            'moderation_date' => $this->resource->updated_at,
        ];
    }
}
