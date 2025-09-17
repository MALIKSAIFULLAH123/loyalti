<?php

namespace MetaFox\Search\Http\Resources\v1\Search;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Search\Models\Search as Model;

/**
 * Class SearchItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SearchItem extends JsonResource
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
        $item = $this->resource->item;

        $resource = ResourceGate::asResource($item, 'item', null);

        if ($resource instanceof JsonResource) {
            return $resource->toArray($request);
        }

        return [];
    }
}
