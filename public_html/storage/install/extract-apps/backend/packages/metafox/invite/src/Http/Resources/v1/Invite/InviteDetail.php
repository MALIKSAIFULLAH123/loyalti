<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Invite\Models\Invite as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class InviteDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class InviteDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->id,
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'email'         => $this->resource->email,
            'is_pending'    => $this->resource->isPending(),
            'phone_number'  => $this->resource->phone_number,
            'expired_at'    => $this->resource->expired_at,
            'created_at'    => $this->resource->created_at,
            'updated_at'    => $this->resource->updated_at,
        ];
    }
}
