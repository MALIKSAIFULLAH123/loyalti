<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Photo\Models\Album as Model;
use MetaFox\Photo\Support\Traits\Album\AlbumTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;

/**
 * Class AlbumEmbed.
 * @property Model $resource
 */
class FeedEmbed extends JsonResource
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
        $postOnOther   = $this->resource->userId() != $this->resource->ownerId();

        $ownerResource = null;

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'photo',
            'resource_name'     => $this->resource->entityType(),
            'privacy'           => $this->resource->privacy,
            'items'             => $this->getAlbumItems(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'parent_user'       => $ownerResource,
            'info'              => __p('photo::web.created_a_photo_album'),
            'total_item'        => $this->resource->total_item,
            'statistic'         => $this->getStatistic(),
            'feed_id'           => $this->getFeedParams()->resource->entityId(),
            'is_featured'       => $this->resource->is_featured,
            'is_sponsor'        => $this->resource->is_sponsor,
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
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
