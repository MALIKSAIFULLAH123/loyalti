<?php

namespace MetaFox\Search\Http\Resources\v1\Search;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SuggestionItem extends JsonResource
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
        $item = $this->resource;

        $resource = app('events')->dispatch($item->entityType() . '.get_search_resource', [$item], true);

        if ($resource instanceof JsonResource) {
            return $resource->toArray($request);
        }

        return [];
    }
}
