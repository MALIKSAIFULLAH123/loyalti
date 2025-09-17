<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Forum\Models\Forum as Model;
use MetaFox\Forum\Support\Browse\Traits\Forum\ExtraTrait;
use MetaFox\Forum\Support\Browse\Traits\Forum\StatisticTrait;

/**
 * Class GroupItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ForumItem extends JsonResource
{
    use ExtraTrait;
    use StatisticTrait;

    /**
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource = $this->resource;

        $title = parse_output()->parse($resource->toTitle());

        $statistic = $this->getStatistic();

        $subLink = 0 == Arr::get($statistic, 'total_sub_forum', 0) ? null : $this->resource->toSubLinkAdminCP();

        return [
            'id'                => $resource->entityId(),
            'resource_name'     => $resource->entityType(),
            'module_name'       => 'forum',
            'title'             => $title,
            'description'       => $resource->description,
            'statistic'         => $statistic,
            'creation_date'     => $resource->getCreatedAt(),
            'modification_date' => $resource->getUpdatedAt(),
            'extra'             => $this->getForumExtra(),
            'parent'            => $this->getParent(),
            'is_closed'         => $resource->is_closed,
            'is_opened'         => !$resource->is_closed,
            'sub_link'          => $subLink,
            'link'              => $resource->toLink(),
            'url'               => $resource->toUrl(),
        ];
    }

    protected function getParent(): ?array
    {
        if (null === $this->resource->parentForums) {
            return null;
        }

        return [
            'id'            => $this->resource->parentForums->entityId(),
            'resource_name' => $this->resource->parentForums->entityType(),
            'module_name'   => 'forum',
            'title'         => parse_output()->parse($this->resource->parentForums->toTitle()),
        ];
    }
}
