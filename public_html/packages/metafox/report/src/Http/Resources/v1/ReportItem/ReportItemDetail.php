<?php

namespace MetaFox\Report\Http\Resources\v1\ReportItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Report\Models\ReportItem as Model;

/**
 * Class ReportItemDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ReportItemDetail extends JsonResource
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
            'id'                => $this->resource->entityId(),
            'module_name'       => 'report',
            'resource_name'     => $this->resource->entityType(),
            'embed_object'      => ResourceGate::asEmbed($this->resource->item),
            'feedback'          => $this->resource->feedback,
            'reason'            => $this->resource->reason?->name,
            'ip_address'        => $this->resource->ip_address,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
