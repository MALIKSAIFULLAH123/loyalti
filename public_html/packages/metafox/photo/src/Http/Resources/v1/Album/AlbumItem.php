<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\Album as Model;
use MetaFox\Photo\Support\Traits\Album\ExtraTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;

/**
 * Class AlbumItem.
 * @property Model $resource
 */
class AlbumItem extends JsonResource
{
    use ExtraTrait;
    use HasStatistic;

    /**
     * @inheritdoc
     *
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        return [
            'total_photo'   => $this->resource->total_photo,
            'total_video'   => $this->resource->total_video,
            'total_item'    => $this->resource->total_item,
            'total_like'    => $this->resource->total_like,
            'total_share'   => $this->resource->total_share,
            'total_comment' => $this->resource->total_comment, // @todo improve or remove.
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context = user();
        $itemId  = $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0;

        $userEntity   = $this->resource->userEntity;
        $ownerEntity  = $this->resource->ownerEntity;
        $description  = '';
        $albumText    = $this->resource->albumText;
        if ($albumText) {
            $description = parse_output()->getDescription($albumText->text_parsed);
        }

        return [
            'id'                => $this->resource->id,
            'module_name'       => 'photo',
            'resource_name'     => $this->resource->entityType(),
            'name'              => ban_word()->clean($this->resource->name),
            'description'       => $description,
            'text'              => $albumText?->text ?? '',
            'text_parsed'       => parse_output()->parseItemDescription($albumText?->text_parsed ?? ''),
            'module_id'         => $this->resource->ownerType() != 'user' ? $this->resource->ownerType() : null, //Todo:
            'group_id'          => $itemId,
            'item_id'           => $itemId,
            'image'             => $this->resource->images,
            'album_type'        => $this->resource->album_type,
            'user'              => new UserEntityDetail($userEntity),
            'owner'             => new UserEntityDetail($ownerEntity),
            'privacy'           => $this->resource->privacy,
            'is_pending'        => !$this->resource->is_approved,
            'is_featured'       => $this->resource->is_featured,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_saved'          => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'profile_id'        => 0,
            'timeline_id'       => 0,
            'cover_id'          => 0,
            'sponsor_in_feed'   => $this->resource->sponsor_in_feed,
            'statistic'         => $this->getStatistic(),
            'extra'             => $this->getExtra(),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
        ];
    }
}
