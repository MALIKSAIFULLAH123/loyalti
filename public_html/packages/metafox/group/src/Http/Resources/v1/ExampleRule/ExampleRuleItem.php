<?php

namespace MetaFox\Group\Http\Resources\v1\ExampleRule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\ExampleRule as Model;

/**
 * Class ExampleRuleItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ExampleRuleItem extends JsonResource
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
            'module_name'   => '',
            'resource_name' => $this->resource->entityType(),
            'title'         => __p($this->resource->title),
            'description'   => __p($this->resource->description),
            'is_active'     => $this->resource->is_active,
            'ordering'      => $this->resource->ordering,
        ];
    }
}
