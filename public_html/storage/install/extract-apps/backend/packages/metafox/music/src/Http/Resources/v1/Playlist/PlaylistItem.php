<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Http\Resources\v1\Attachment\AttachmentItemCollection;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Support\Browse\Traits\Playlist\ExtraTrait;
use MetaFox\Music\Support\Browse\Traits\Playlist\StatisticTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;

/**
 * Class PlaylistDetail.
 * @property Playlist $resource
 */
class PlaylistItem extends JsonResource
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
            'description'   => $this->getShortDescription(),
            'text'          => $this->getDescription(),
            'is_sponsor'    => $this->resource->is_sponsor,
            'privacy'       => $this->resource->privacy,
            'is_liked'      => $this->isLike($context),
            'is_saved'      => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'module_id'     => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'       => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'image'         => $this->resource->images,
            'statistic'     => $this->getStatistic(),
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'owner'         => ResourceGate::user($this->resource->ownerEntity),
            // 'attachments'       => new AttachmentItemCollection($this->resource->attachments),
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'creation_date'     => $this->resource->created_at,
            'extra'             => $this->getExtra(),
            'modification_date' => $this->resource->updated_at,
            // 'feed_param'        => $this->getFeedParams(),
        ];
    }
}
