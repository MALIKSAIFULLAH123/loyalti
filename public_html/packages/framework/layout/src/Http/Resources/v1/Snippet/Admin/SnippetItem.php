<?php

namespace MetaFox\Layout\Http\Resources\v1\Snippet\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Layout\Models\Snippet as Model;

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
class SnippetItem extends JsonResource
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
            'module_name'   => '',
            'resource_name' => $this->resource->entityType(),
            'created_at'    => $this->resource->created_at,
            'updated_at'    => $this->resource->updated_at,
            'is_active'     => $this->resource->is_active,
            'links'         => [
                'revision' => '/layout/revision/browse?snippet=' . $this->resource->id,
            ],
            'theme' => $this->resource->theme,
            'name'  => $this->resource->name,
        ];
    }
}
