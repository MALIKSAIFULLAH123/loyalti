<?php

namespace MetaFox\User\Http\Resources\v1\CancelReason\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\CancelReason as Model;

/**
 * Class CancelReasonItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CancelReasonItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'user',
            'resource_name' => $this->resource->entityType(),
            'phrase_var'    => $this->resource->phrase_var,
            'title'         => $this->resource->title,
            'is_active'     => (bool) $this->resource->is_active,
        ];
    }
}
