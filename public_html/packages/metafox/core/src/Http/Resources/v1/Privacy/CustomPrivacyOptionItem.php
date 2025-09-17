<?php

namespace MetaFox\Core\Http\Resources\v1\Privacy;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Contracts\HasTitle;

class CustomPrivacyOptionItem extends JsonResource
{
    public function toArray($request)
    {
        $title = null;

        if ($this->resource instanceof HasTitle) {
            $title = $this->resource->toTitle();
        }

        $isSelected = $this->resource?->is_selected;

        return [
            'id'            => $this->resource->entityId(),
            'resource_name' => $this->resource->entityType(),
            'module_name'   => $this->resource->moduleName(),
            'title'         => $title,
            'is_selected'   => $isSelected ?? false,
        ];
    }
}
