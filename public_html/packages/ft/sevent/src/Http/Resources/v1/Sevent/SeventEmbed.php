<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Illuminate\Http\Request;
use Foxexpert\Sevent\Models\Sevent;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class SeventEmbed.
 * @property Sevent $resource
 */
class SeventEmbed extends SeventItem
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

        if ($this->resource->seventText) {
            $shortDescription = parse_output()->getDescription($this->resource->seventText->text_parsed);
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'title'         => $this->resource->title,
            'description'   => $shortDescription,
            'image'         => $this->resource->image,
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
