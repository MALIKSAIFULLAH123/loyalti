<?php

namespace MetaFox\Blog\Http\Resources\v1\Blog;

use Illuminate\Http\Request;
use MetaFox\Blog\Models\Blog;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class BlogEmbed.
 * @property Blog $resource
 */
class BlogEmbed extends BlogItem
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $shortDescription = '';

        if ($this->resource->blogText) {
            $shortDescription = parse_output()->getDescription($this->resource->blogText->text_parsed);
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'title'         => ban_word()->clean($this->resource->title),
            'description'   => $shortDescription,
            'image'         => $this->resource->images,
            'user'          => ResourceGate::user($this->resource->userEntity),
            'privacy'       => $this->resource->privacy,
            'is_featured'   => $this->resource->is_featured,
            'is_sponsor'    => $this->resource->is_sponsor,
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'statistic'     => $this->getStatistic(),
        ];
    }
}
