<?php

namespace MetaFox\Report\Http\Resources\v1\ReportOwner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Report\Models\ReportOwnerUser as Model;

/**
 * Class ReportOwnerItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Reporter extends JsonResource
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
            'user_id'           => $this->resource->userId(),
            'user_type'         => $this->resource->userType(),
            'report_id'         => $this->resource->report_id,
            'reason_id'         => $this->resource->reason_id,
            'reason'            => $this->resource->reason,
            'feedback'          => $this->resource->feedback,
            'ip_address'        => $this->resource->ip_address,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
