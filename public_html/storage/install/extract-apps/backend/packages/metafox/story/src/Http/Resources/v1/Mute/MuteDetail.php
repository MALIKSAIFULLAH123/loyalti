<?php

namespace MetaFox\Story\Http\Resources\v1\Mute;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Story\Models\Mute as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class MuteDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class MuteDetail extends JsonResource
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
            'module_name'   => 'story',
            'resource_name' => $this->resource->entityType(),
            'owner'         => ResourceGate::user($this->ownerEntity),
        ];
    }
}
