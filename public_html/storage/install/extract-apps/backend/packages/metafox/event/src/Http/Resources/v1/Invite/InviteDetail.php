<?php

namespace MetaFox\Event\Http\Resources\v1\Invite;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Event\Models\Invite as Model;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class InviteDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class InviteDetail extends JsonResource
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
            'module_name'   => 'event',
            'resource_name' => $this->resource->entityType(),
            'status_id'     => $this->resource->status_id,
            'event_id'      => $this->resource->event_id,
            'user'          => ResourceGate::user($this->resource->userEntity),
            'owner'         => ResourceGate::user($this->resource->ownerEntity),
        ];
    }
}
