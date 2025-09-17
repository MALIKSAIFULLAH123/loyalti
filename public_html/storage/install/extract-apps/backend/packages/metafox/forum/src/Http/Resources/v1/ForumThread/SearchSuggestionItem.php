<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchSuggestionItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = $this->resource;

        $id = $resource->entityId();

        $title = parse_output()->parse($resource->toTitle());

        return [
            'label' => $title,
            'value' => $id,
        ];
    }
}
