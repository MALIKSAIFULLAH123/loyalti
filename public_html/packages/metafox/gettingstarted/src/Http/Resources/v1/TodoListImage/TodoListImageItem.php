<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoListImage;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\MetaFoxConstant;

class TodoListImageItem extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'getting-started',
            'resource_name' => $this->resource->entityType(),
            'image'         => $this->resource->images,
            'status'        => MetaFoxConstant::FILE_UPDATE_STATUS,
        ];
    }
}
