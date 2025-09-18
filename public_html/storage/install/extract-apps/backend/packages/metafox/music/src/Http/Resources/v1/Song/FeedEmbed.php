<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Http\Resources\v1\Album\AlbumDetail;
use MetaFox\Music\Http\Resources\v1\Genre\GenreEmbedCollection;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Support\Browse\Traits\Song\ExtraTrait;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/**
 * Class SongEmbed.
 *
 * @property Song $resource
 */
class FeedEmbed extends JsonResource
{
    use HasStatistic;
    use HasDescriptionTrait;
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $context = user();

        $album = $this->resource->album ?? null;

        if (null !== $album) {
            $album = new AlbumDetail($album);
        }

        $postOnOther = $this->resource->userId() != $this->resource->ownerId();

        $ownerResource = null;

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->moduleName(),
            'resource_name' => $this->resource->entityType(),
            'name'          => ban_word()->clean($this->resource->name),
            'file_name'     => $this->resource->original_name,
            'description'   => $this->getShortDescription(),
            'text'          => $this->getDescription(),
            'is_featured'   => $this->resource->is_featured,
            'is_sponsor'    => $this->resource->is_sponsor,
            'privacy'       => $this->resource->privacy,
            'is_saved'      => PolicyGate::check(
                $this->resource->entityType(),
                'isSavedItem',
                [$context, $this->resource]
            ),
            'module_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'duration'          => $this->resource->duration,
            'image'             => $this->resource->images,
            'statistic'         => $this->getStatistic(),
            'link'              => $this->resource->toLink(),
            'destination'       => $this->resource->link_media_file,
            'url'               => $this->resource->toUrl(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'parent_user'       => $ownerResource,
            'info'              => 'added_a_song',
            'attachments'       => ResourceGate::items($this->resource->attachments, false),
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'genres'            => new GenreEmbedCollection($this->resource->genres),
            'view_id'           => $this->resource->view_id,
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
            'album_id'          => $this->resource->album_id,
            'album'             => $album,
            'extra'             => $this->getExtra(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        $reactItem = $this->resource->reactItem();

        return [
            'total_like'                                  => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_comment'                               => $reactItem instanceof HasTotalComment ? $reactItem->total_comment : 0,
            'total_share'                                 => $this->resource->total_share,
            'total_view'                                  => $this->resource->total_view,
            $this->resource->entityType() . '_total_play' => $this->resource->total_play,
            'total_reply'                                 => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
            'total_rating'                                => $this->resource->total_rating,
            'total_score'                                 => $this->resource->total_score,
        ];
    }
}
