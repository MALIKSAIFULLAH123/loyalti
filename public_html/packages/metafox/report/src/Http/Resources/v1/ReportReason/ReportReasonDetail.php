<?php

namespace MetaFox\Report\Http\Resources\v1\ReportReason;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Report\Models\ReportReason as Model;

/**
 * Class ReportReasonDetail.
 * @property Model $resource
 */
class ReportReasonDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id'                => $this->resource->id,
            'module_name'       => 'report',
            'resource_name'     => $this->resource->entityType(),
            'name'              => $this->resource->name,
            'ordering'          => $this->resource->ordering,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
