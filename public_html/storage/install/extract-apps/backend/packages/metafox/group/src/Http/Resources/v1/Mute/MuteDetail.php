<?php

namespace MetaFox\Group\Http\Resources\v1\Mute;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Mute as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class MuteDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class MuteDetail extends JsonResource
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
            'id'            => $this->id,
            'module_name'   => '',
            'resource_name' => $this->entityType(),
        ];
    }
}
