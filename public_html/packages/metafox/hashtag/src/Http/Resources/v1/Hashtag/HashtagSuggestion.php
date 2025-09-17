<?php

namespace MetaFox\Hashtag\Http\Resources\v1\Hashtag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Hashtag\Models\Tag as Model;

/**
 * Class HashtagSuggestion.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class HashtagSuggestion extends JsonResource
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
        $link = '/hashtag/search?q=%23' . $this->resource->tag_url;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'core',
            'resource_name' => $this->resource->entityType(),
            'entity_type'   => $this->resource->entityType(),
            'text'          => '#' . $this->resource->text,
            'link'          => $link,
            'url'           => $link,
            'router'        => url_utility()->makeApiMobileUrl($link),
        ];
    }
}
