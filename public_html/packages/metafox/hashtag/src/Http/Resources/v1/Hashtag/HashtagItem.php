<?php

namespace MetaFox\Hashtag\Http\Resources\v1\Hashtag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Hashtag\Models\Tag as Model;

/**
 * Class HashtagItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class HashtagItem extends JsonResource
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
        $hashtag = '#' . $this->resource->text;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'core',
            'resource_name' => $this->resource->entityType(),
            'text'          => $hashtag,
            'tag_url'       => $this->resource->tag_url,
            'link'          => $this->resource->toLink(),
            'statistic'     => [
                'total_post' => $this->resource->total_item,
            ],
        ];
    }
}
