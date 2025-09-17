<?php

namespace MetaFox\User\Http\Resources\v1\InactiveProcess\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Models\InactiveProcess as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class InactiveProcessItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class InactiveProcessItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'user',
            'resource_name' => $this->resource->entityType(),
            'total_users'   => $this->resource->total_users,
            'total_sent'    => $this->resource->total_sent,
            'status'        => $this->resource->status,
            'user'          => ResourceGate::user($this->resource->userEntity),
            'status_text'   => $this->resource->statusText(),
            'process_text'  => $this->resource->processText(),
            'created_at'    => $this->resource->created_at,
        ];
    }
}
