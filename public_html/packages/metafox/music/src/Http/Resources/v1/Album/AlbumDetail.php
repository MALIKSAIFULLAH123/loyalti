<?php

namespace MetaFox\Music\Http\Resources\v1\Album;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Http\Resources\v1\Song\SongPlayCollection;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Policies\AlbumPolicy;
use MetaFox\Music\Repositories\AlbumRepositoryInterface;
use MetaFox\Music\Support\Browse\Traits\Album\StatisticTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;

/**
 * Class AlbumDetail.
 * @property Album $resource
 */
class AlbumDetail extends JsonResource
{
    use StatisticTrait;
    use HasExtra;
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
            'id'              => $this->resource->entityId(),
            'module_name'     => $this->resource->moduleName(),
            'resource_name'   => $this->resource->entityType(),
            'name'            => ban_word()->clean($this->resource->name),
            'description'     => $this->getShortDescription(['albumText' => 'text_parsed']),
            'text'            => $this->getDescription(['albumText' => 'text_parsed']),
            'is_featured'     => $this->resource->is_featured,
            'is_sponsor'      => $this->resource->is_sponsor,
            'privacy'         => $this->resource->privacy,
            'is_liked'        => $this->isLike($context),
            'is_saved'        => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'module_id'       => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'image'           => $this->resource->images,
            'statistic'       => $this->getStatistic(),
            'link'            => $this->resource->toLink(),
            'url'             => $this->resource->toUrl(),
            'user'            => ResourceGate::user($this->resource->userEntity),
            'owner'           => ResourceGate::user($this->resource->ownerEntity),
            'attachments'     => ResourceGate::items($this->resource->attachments, false),
            'owner_type_name' => __p_type_key($this->resource->ownerType()),
            'extra'           => $this->getExtra(),
            'view_id'         => $this->resource->view_id,
            'creation_date'   => $this->resource->created_at,
            'year'            => $this->resource->year,
            'feed_param'      => $this->getFeedParams(),
            'initial_songs'   => $this->getInitialSongs(),
            'info'            => 'created_a_music_album',
        ];
    }

    protected function getInitialSongs(): ResourceCollection
    {
        $context = user();

        if (!policy_check(AlbumPolicy::class, 'view', $context, $this->resource)) {
            return new SongPlayCollection([]);
        }

        $songs = resolve(AlbumRepositoryInterface::class)->viewAlbumItems($context, $this->resource->entityId());

        return new SongPlayCollection($songs);
    }
}
