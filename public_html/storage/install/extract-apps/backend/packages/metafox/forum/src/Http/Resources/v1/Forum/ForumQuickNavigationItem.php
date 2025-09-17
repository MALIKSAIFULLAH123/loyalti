<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Forum\Models\Forum as Model;
use MetaFox\Forum\Support\Browse\Traits\Forum\ExtraTrait;
use MetaFox\Forum\Support\Browse\Traits\Forum\StatisticTrait;

/**
 * Class GroupItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ForumQuickNavigationItem extends JsonResource
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
            'id'            => $resource->entityId(),
            'resource_name' => $resource->entityType(),
            'module_name'   => 'forum',
            'title'         => $title,
            'description'   => $resource->description,
            'statistic'     => $this->getStatistic(),
            'extra'         => $this->getForumExtra(),
            'is_opened'     => !$this->resource->is_closed,
            'subs'          => new ForumQuickNavigationCollection($resource->subForums),
        ];
    }
}
