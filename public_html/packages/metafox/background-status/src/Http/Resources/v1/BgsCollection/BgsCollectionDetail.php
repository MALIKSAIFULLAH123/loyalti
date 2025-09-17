<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsBackground\BgsBackgroundDetail;
use MetaFox\BackgroundStatus\Http\Resources\v1\BgsBackground\BgsBackgroundItemCollection;
use MetaFox\BackgroundStatus\Models\BgsCollection as Model;

/**
 * Class BgsCollectionDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class BgsCollectionDetail extends JsonResource
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
        $image = null;
        $mainBackground = $this->resource->mainBackground;
        if (null != $mainBackground) {
            $image = $mainBackground->images;
        }

        return [
            'id'                 => $this->resource->entityId(),
            'module_name'        => 'background-status',
            'resource_name'      => $this->resource->entityType(),
            'is_default'         => $this->resource->is_default,
            'is_active'          => $this->resource->is_active,
            'image'              => $image,
            'main_background_id' => $this->resource->main_background_id,
            'mainBackground'     => new BgsBackgroundDetail($mainBackground),
            'view_only'          => $this->resource->view_only,
            'is_deleted'         => $this->resource->is_deleted,
            'total_background'   => $this->resource->total_background,
            'backgrounds'        => new BgsBackgroundItemCollection($this->resource->backgrounds),
        ];
    }
}
