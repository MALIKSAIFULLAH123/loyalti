<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Forum\Support\Browse\Traits\Forum\ExtraTrait;
use MetaFox\Forum\Support\Browse\Traits\Forum\StatisticTrait;

class ForumSideBlockItem extends JsonResource
{
    use StatisticTrait;
    use ExtraTrait;

    /**
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = $this->resource;

        $title = parse_output()->parse($resource->toTitle());

        return [
            'id'                => $resource->entityId(),
            'resource_name'     => $resource->entityType(),
            'module_name'       => 'forum',
            'name'              => $title,
            'description'       => $resource->description,
            'link'              => $resource->toLink(),
            'url'               => $resource->toUrl(),
            'statistic'         => $this->getStatistic(),
            'is_opened'         => !$this->resource->is_closed,
            'creation_date'     => $resource->getCreatedAt(),
            'modification_date' => $resource->getUpdatedAt(),
            'extra'             => $this->getForumExtra(),
            'subs'              => new ForumSideBlockItemCollection($resource->subForums),
        ];
    }
}
