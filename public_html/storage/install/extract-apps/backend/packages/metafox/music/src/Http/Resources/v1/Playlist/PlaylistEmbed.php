<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Support\Browse\Traits\Playlist\StatisticTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class PlaylistEmbed.
 * @property Playlist $resource
 */
class PlaylistEmbed extends JsonResource
{
    use StatisticTrait;
    use HasDescriptionTrait;

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

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->moduleName(),
            'resource_name' => $this->resource->entityType(),
            'name'          => ban_word()->clean($this->resource->name),
            'description'   => $this->getShortDescription(),
            'text'          => $this->getDescription(),
            'is_sponsor'    => $this->resource->is_sponsor,
            'privacy'       => $this->resource->privacy,
            'is_saved'      => PolicyGate::check(
                $this->resource->entityType(),
                'isSavedItem',
                [$context, $this->resource]
            ),
            'module_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'image'             => $this->resource->images,
            'statistic'         => $this->getStatistic(),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'attachments'       => ResourceGate::items($this->resource->attachments, false),
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
