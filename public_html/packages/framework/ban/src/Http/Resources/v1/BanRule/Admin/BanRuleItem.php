<?php

namespace MetaFox\Ban\Http\Resources\v1\BanRule\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Ban\Models\BanRule as Model;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class BanRuleItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class BanRuleItem extends JsonResource
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
            'module_name'   => 'ban',
            'resource_name' => $this->resource->entityType(),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'find_value'    => $this->resource->find_value,
            'is_active'     => $this->resource->is_active,
            'replacement'   => $this->resource->replacement,
            'created_at'    => $this->resource->created_at,
        ];
    }
}
