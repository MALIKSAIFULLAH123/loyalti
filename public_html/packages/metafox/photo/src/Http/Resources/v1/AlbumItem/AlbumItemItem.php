<?php

namespace MetaFox\Photo\Http\Resources\v1\AlbumItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\AlbumItem as Model;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class AlbumItem.
 * @property Model $resource
 */
class AlbumItemItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return array<string,           mixed>
     */
    public function toArray($request): array
    {
        $resource = ResourceGate::asDetail($this->resource->detail, null);

        if (!$resource instanceof JsonResource) {
            return [];
        }

        return $resource->toArray($request);
    }
}
