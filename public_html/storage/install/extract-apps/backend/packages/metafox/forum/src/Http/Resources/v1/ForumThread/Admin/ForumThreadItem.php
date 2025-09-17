<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Forum\Models\ForumThread as Model;
use MetaFox\Forum\Support\Browse\Traits\ForumThread\ExtraTrait;
use MetaFox\Forum\Support\Browse\Traits\ForumThread\StatisticTrait;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ForumThreadItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ForumThreadItem extends JsonResource
{
    use HasHashtagTextTrait;
    use StatisticTrait;
    use ExtraTrait;
    use HasFeedParam;
    use IsLikedTrait;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $resource     = $this->resource;
        $itemResource = null;

        if (null !== $resource->item_type && $resource->item_id > 0) {
            $item = $resource->getItem();

            if (null !== $item) {
                $itemResource = ResourceGate::asResource($item->refresh(), 'integration');
            }
        }

        return [
            'id'                => $resource->entityId(),
            'resource_name'     => $resource->entityType(),
            'module_name'       => 'forum',
            'title'             => $resource->toTitle(),
            'short_description' => $resource->short_description,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'item'              => $itemResource,
            'statistic'         => $this->getStatistic(),
            'is_closed'         => (bool) $resource->isClosed(),
            'is_wiki'           => (bool) $resource->isWiki(),
            'is_approved'       => (bool) $resource->isApproved(),
            'is_sponsored'      => (bool) $resource->isSponsor(),
            'link'              => $resource->toLink(),
            'url'               => $resource->toUrl(),
            'creation_date'     => $this->convertDate($resource->getCreatedAt()),
            'modification_date' => $this->convertDate($resource->getUpdatedAt()),
            'extra'             => $this->getThreadExtra(),
        ];
    }

    protected function convertDate(?string $date): ?string
    {
        if (null == $date) {
            return null;
        }

        return Carbon::parse($date)->format('c');
    }

    protected function handleTitle(string $title): string
    {
        $title = ban_word()->clean($title);
        $title = ban_word()->parse($title);

        return $title;
    }
}
