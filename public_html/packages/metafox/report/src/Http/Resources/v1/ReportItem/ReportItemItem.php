<?php

namespace MetaFox\Report\Http\Resources\v1\ReportItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Report\Models\ReportItem as Model;

/**
 * Class ReportItemItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ReportItemItem extends JsonResource
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
            'reason'            => $this->resource->reason?->name,
            'embed_object'      => ResourceGate::asEmbed($this->resource->item),
            'ip_address'        => $this->resource->ip_address,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
