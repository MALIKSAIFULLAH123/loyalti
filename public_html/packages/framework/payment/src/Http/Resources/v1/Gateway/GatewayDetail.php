<?php

namespace MetaFox\Payment\Http\Resources\v1\Gateway;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Payment\Models\Gateway as Model;

/**
 * Class GatewayDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class GatewayDetail extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request      $request
     * @return array<mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'payment',
            'resource_name' => $this->resource->entityType(),
            'service'       => $this->resource->service,
            'is_active'     => $this->resource->is_active,
            'is_test'       => $this->resource->is_test,
            'title'         => $this->resource->title,
            'description'   => $this->resource->description,
        ];
    }
}
