<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Http\Resources\v1\Album\AlbumDetail;
use MetaFox\Music\Http\Resources\v1\Genre\GenreEmbedCollection;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Support\Browse\Traits\Song\ExtraTrait;
use MetaFox\Music\Support\Browse\Traits\Song\StatisticTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;

/**
 * Class SongDetail.
 *
 * @property Song $resource
 */
class SongDetail extends JsonResource
{
    use StatisticTrait;
    use ExtraTrait;
    use HasFeedParam;
    use IsLikedTrait;
    use HasDescriptionTrait;

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

        $album = $this->resource->album ?? null;

        if (null !== $album) {
            $album = new AlbumDetail($album);
        }
        $reactItem = $this->resource->reactItem();

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
            'is_liked'      => $this->isLike($context),
            'is_pending'    => !$this->resource->isApproved(),
            'is_approved'   => $this->resource->isApproved(),
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
            'attachments'       => ResourceGate::items($this->resource->attachments, false),
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'genres'            => new GenreEmbedCollection($this->resource->genres),
            'creation_date'     => $this->resource->created_at,
            'extra'             => $this->getExtra(),
            'view_id'           => $this->resource->view_id,
            'modification_date' => $this->resource->updated_at,
            'feed_param'        => $this->getFeedParams(),
            'album_id'          => $this->resource->album_id,
            'album'             => $album,
            'info'              => 'added_a_song',
            'comment_item_id'   => $reactItem->entityId(),
            'comment_type_id'   => $reactItem->entityType(),
        ];
    }
}
