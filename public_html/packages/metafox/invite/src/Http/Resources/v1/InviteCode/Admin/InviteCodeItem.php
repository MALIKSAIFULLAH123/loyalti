<?php

namespace MetaFox\Invite\Http\Resources\v1\InviteCode\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Invite\Models\InviteCode as Model;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class InviteCodeItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class InviteCodeItem extends JsonResource
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
            'module_name'   => 'invite',
            'resource_name' => $this->resource->entityType(),
            'code'          => $this->resource->code,
            'is_active'     => $this->resource->is_active,
            'user_id'       => $this->resource->user_id,
            'user_name'     => $this->resource->user?->display_name,
            'created_at'    => $this->resource->created_at,
            'updated_at'    => $this->resource->updated_at,
            'user'          => ResourceGate::user($this->resource->userEntity),
        ];
    }
}
