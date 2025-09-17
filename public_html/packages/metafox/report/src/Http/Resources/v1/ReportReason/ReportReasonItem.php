<?php

namespace MetaFox\Report\Http\Resources\v1\ReportReason;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Report\Models\ReportReason as Model;

/**
 * Class ReportReasonItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ReportReasonItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request       $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'                => $this->resource->id,
            'module_name'       => 'report',
            'resource_name'     => $this->resource->entityType(),
            'name'              => $this->resource->name,
            'ordering'          => $this->resource->ordering,
        ];
    }
}
