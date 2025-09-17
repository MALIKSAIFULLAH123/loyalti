<?php

namespace MetaFox\Invite\Http\Resources\v1\InviteCode;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Invite\Models\InviteCode as Model;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class InviteCodeDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class InviteCodeDetail extends JsonResource
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
            'id'            => $this->resource->entityType(),
            'module_name'   => 'invite',
            'resource_name' => $this->entityType(),
            'code'          => $this->resource->code,
            'is_active'     => $this->resource->is_active,
            'user'          => ResourceGate::user($this->resource->userEntity),
        ];
    }
}
