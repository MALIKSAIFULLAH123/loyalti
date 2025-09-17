<?php

namespace MetaFox\Notification\Http\Resources\v1\NotificationChannel\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Notification\Models\NotificationChannel as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class NotificationChannelItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class NotificationChannelItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'notification',
            'resource_name' => $this->resource->entityType(),
            'title'         => $this->resource->toTitle(),
            'is_active'     => $this->resource->is_active,
            'disable'       => $this->resource->isDisable(),
            'is_ready'      => !$this->resource->isDisable(),
        ];
    }
}
