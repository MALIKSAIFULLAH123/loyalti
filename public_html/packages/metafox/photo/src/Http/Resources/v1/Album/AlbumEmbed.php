<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\Album as Model;
use MetaFox\Photo\Support\Traits\Album\AlbumTrait;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;

/**
 * Class AlbumEmbed.
 * @property Model $resource
 */
class AlbumEmbed extends JsonResource
{
    use AlbumTrait;
    use HasFeedParam;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'photo',
            'resource_name' => $this->resource->entityType(),
            'name'          => ban_word()->clean($this->resource->name),
            'privacy'       => $this->resource->privacy,
            'items'         => $this->getAlbumItems(), //this is issue slow performance
            'user'          => ResourceGate::user($this->resource->userEntity),
            'total_item'    => $this->resource->total_item,
            'statistic'     => $this->getStatistic(),
            'feed_id'       => $this->getFeedParams()->resource->entityId(),
            'is_featured'   => $this->resource->is_featured,
            'is_sponsor'    => $this->resource->is_sponsor,
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        $reactItem = $this->resource->reactItem();

        return [
            'total_photo'   => $this->resource->total_photo,
            'total_item'    => $this->resource->total_item,
            'total_like'    => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_share'   => $this->resource->total_share,
            'total_comment' => $reactItem instanceof HasTotalComment ? $reactItem->total_comment : 0,
            'total_reply'   => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
        ];
    }
}
