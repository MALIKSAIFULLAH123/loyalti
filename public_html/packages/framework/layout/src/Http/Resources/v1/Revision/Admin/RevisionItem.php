<?php

namespace MetaFox\Layout\Http\Resources\v1\Revision\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Layout\Models\Revision as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class SnippetItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class RevisionItem extends JsonResource
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
            'id'         => $this->resource->id,
            'name'       => $this->resource->name,
            'snippet_id' => $this->resource->snippet_id,
            'active'     => $this->resource->snippet?->revision_id == $this->resource->id,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'can_revert' => true,
        ];
    }
}
