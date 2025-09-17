<?php

namespace MetaFox\Layout\Http\Resources\v1\Revision\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Layout\Models\Revision as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class RevisionDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class RevisionDetail extends JsonResource
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
            'id'   => $this->id,
            'name' => $this->name,
            'data' => $this->resource->data,
        ];
    }
}
