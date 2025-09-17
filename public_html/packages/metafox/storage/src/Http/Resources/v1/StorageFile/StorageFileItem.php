<?php

namespace MetaFox\Storage\Http\Resources\v1\StorageFile;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Storage\Models\StorageFile;

/**
 * @property StorageFile $resource
 */
class StorageFileItem extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'original_name' => $this->resource->original_name,
            'item_type'     => $this->resource->item_type,
            'user_id'       => $this->resource->user_id,
            'user_type'     => $this->resource->user_type,
            'width'         => $this->resource->width,
            'height'        => $this->resource->height,
            'temp_file'     => $this->resource->id,
            'url'           => $this->resource->url,
        ];
    }
}
