<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\Album as Model;
use MetaFox\Photo\Support\Traits\Album\AlbumTrait;
use MetaFox\Photo\Support\Traits\Album\ExtraTrait;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/**
 * Class AlbumDetail.
 * @property Model $resource
 */
class AlbumDetail extends JsonResource
{
    use AlbumTrait;
    use ExtraTrait;
    use HasStatistic;
    use HasFeedParam;

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

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context = user();

        $moduleId = null;

        $itemId = 0;

        //fox4 logic: page/group
        if ($this->resource->ownerId() != $this->resource->userId()) {
            $moduleId = $this->resource->ownerType();

            $itemId = $this->resource->ownerId();
        }

        $userEntity = $this->resource->userEntity;

        $ownerEntity = $this->resource->ownerEntity;

        $description = '';

        $albumText = $this->resource->albumText;

        if ($albumText) {
            $description = $albumText->text_parsed;
        }

        $extra = $this->getExtra();

        $feedParams = array_merge($this->getFeedParams()->toArray($request), [
            'items' => $this->getAlbumItems(),
        ]);

        return [
            'id'            => $this->resource->id,
            'module_name'   => 'photo',
            'resource_name' => $this->resource->entityType(),
            'name'          => ban_word()->clean($this->resource->name),
            'text'          => $albumText?->text ?? '',
            'text_parsed'   => parse_output()->parseItemDescription($description),
            'module_id'     => $moduleId,
            'group_id'      => $itemId,
            'item_id'       => $itemId,
            'image'         => $this->resource->images,
            'total_item'    => $this->resource->total_item,
            'album_type'    => $this->resource->album_type,
            'user'          => ResourceGate::user($userEntity),
            'owner'         => ResourceGate::user($ownerEntity),
            'privacy'       => $this->resource->privacy,
            'is_pending'    => !$this->resource->is_approved,
            'is_featured'   => $this->resource->is_featured,
            'is_sponsor'    => $this->resource->is_sponsor,
            'is_saved'      => PolicyGate::check(
                $this->resource->entityType(),
                'isSavedItem',
                [$context, $this->resource]
            ),
            'profile_id'        => 0,
            'timeline_id'       => 0,
            'cover_id'          => 0,
            'sponsor_in_feed'   => $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'statistic'         => $this->getStatistic(),
            'extra'             => $extra,
            'feed_param'        => $feedParams,
            'info'              => 'created_a_photo_album',
        ];
    }
}
