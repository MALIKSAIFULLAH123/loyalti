<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Http\Resources\v1\Attachment\AttachmentItemCollection;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Http\Resources\v1\Album\AlbumEmbed;
use MetaFox\Music\Http\Resources\v1\Genre\GenreItemCollection;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Support\Browse\Traits\Song\ExtraTrait;
use MetaFox\Music\Support\Browse\Traits\Song\StatisticTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;

/**
 * Class SongItem.
 * @property Song $resource
 */
class SongItem extends JsonResource
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
            'is_saved'      => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'module_id'     => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'       => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'duration'      => $this->resource->duration,
            'image'         => $this->resource->images,
            'statistic'     => $this->getStatistic(),
            'link'          => $this->resource->toLink(),
            'destination'   => $this->resource->link_media_file,
            'url'           => $this->resource->toUrl(),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'owner'         => ResourceGate::user($this->resource->ownerEntity),
            // 'attachments'       => new AttachmentItemCollection($this->resource->attachments),
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'genres'            => ResourceGate::embeds($this->resource->genres),
            'creation_date'     => $this->resource->created_at,
            'extra'             => $this->getExtra(),
            'view_id'           => $this->resource->view_id,
            'modification_date' => $this->resource->updated_at,
            // 'feed_param'        => $this->getFeedParams(),
            'album_id' => $this->resource->album_id,
            'album'    => $this->getAlbum(),
        ];
    }

    protected function getAlbum(): ?JsonResource
    {
        if (!$this->resource->album instanceof Album) {
            return null;
        }

        return new AlbumEmbed($this->resource->album);
    }
}
