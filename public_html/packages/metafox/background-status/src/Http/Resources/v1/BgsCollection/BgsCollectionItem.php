<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsBackground\BgsBackgroundDetail;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsBackground\BgsBackgroundItemCollection;
use MetaFox\BackgroundStatus\Models\BgsCollection as Model;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class BgsCollectionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class BgsCollectionItem extends JsonResource
{
    public bool $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $mainBackground = $this->resource->mainBackground;

        return [
            'id'               => $this->resource->entityId(),
            'module_name'      => 'background-status',
            'resource_name'    => $this->resource->entityType(),
            'is_default'       => $this->resource->is_default,
            'is_active'        => $this->resource->is_active,
            'name'             => $this->resource->title,
            'image'            => $mainBackground?->images,
            'view_only'        => $this->resource->view_only,
            'is_deleted'       => $this->resource->is_deleted,
            'total_background' => $this->resource->total_background,
            'backgrounds'      => ResourceGate::items($this->resource->backgrounds, false),
        ];
    }
}
