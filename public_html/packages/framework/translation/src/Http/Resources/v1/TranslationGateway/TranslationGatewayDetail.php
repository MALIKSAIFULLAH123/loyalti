<?php

namespace MetaFox\Translation\Http\Resources\v1\TranslationGateway;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Translation\Models\TranslationGateway as Model;

/**
 * Class TranslationGatewayDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TranslationGatewayDetail extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'translation',
            'resource_name' => $this->resource->entityType(),
            'service'       => $this->resource->service,
            'is_active'     => $this->resource->is_active,
            'title'         => $this->resource->title,
            'description'   => $this->resource->description,
        ];
    }
}
