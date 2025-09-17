<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Image;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\MetaFoxConstant;

class ImageItem extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'marketplace',
            'resource_name' => $this->resource->entityType(),
            'image'         => $this->resource->images,
            'status'        => MetaFoxConstant::FILE_UPDATE_STATUS,
        ];
    }
}
