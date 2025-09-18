<?php

namespace MetaFox\Music\Http\Resources\v1\Album;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Support\Browse\Traits\Album\StatisticTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class AlbumEmbed.
 * @property Album $resource
 */
class FeedEmbed extends JsonResource
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

        $postOnOther   = $this->resource->userId() != $this->resource->ownerId();

        $ownerResource = null;

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->moduleName(),
            'resource_name' => $this->resource->entityType(),
            'name'          => ban_word()->clean($this->resource->name),
            'description'   => $this->getShortDescription(['albumText' => 'text_parsed']),
            'text'          => $this->getDescription(['albumText' => 'text_parsed']),
            'is_featured'   => $this->resource->is_featured,
            'is_sponsor'    => $this->resource->is_sponsor,
            'privacy'       => $this->resource->privacy,
            'is_saved'      => PolicyGate::check(
                $this->resource->entityType(),
                'isSavedItem',
                [$context, $this->resource]
            ),
            'image'             => $this->resource->images,
            'statistic'         => $this->getStatistic(),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'parent_user'       => $ownerResource,
            'info'              => 'created_a_music_album',
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'attachments'       => ResourceGate::items($this->resource->attachments, false),
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'view_id'           => $this->resource->view_id,
            'year'              => $this->resource->year,
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
        ];
    }
}
