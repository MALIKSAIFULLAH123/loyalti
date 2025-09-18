<?php

namespace MetaFox\Group\Http\Resources\v1\Rule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Rule as Model;

/**
 * Class RuleItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class RuleItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'group',
            'resource_name' => $this->resource->entityType(),
            'title'         => ban_word()->clean($this->resource->title),
            'description'   => ban_word()->clean($this->resource->description),
            'ordering'      => $this->resource->ordering,
            'group_id'      => $this->resource->group->entityId(),
        ];
    }
}
