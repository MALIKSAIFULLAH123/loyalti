<?php

namespace MetaFox\Group\Http\Resources\v1\GroupInviteCode;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\GroupInviteCode as Model;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class GroupInviteCodeItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class GroupInviteCodeItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'group',
            'resource_name' => $this->resource->entityType(),
            'status'        => false,
            'group_id'      => $this->resource->group_id,
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'user'          => ResourceGate::asResource($this->resource->user, 'detail'),
        ];
    }
}
