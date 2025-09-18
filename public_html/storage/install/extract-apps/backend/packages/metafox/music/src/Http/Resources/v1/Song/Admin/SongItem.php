<?php

namespace MetaFox\Music\Http\Resources\v1\Song\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Http\Resources\v1\Album\AlbumDetail;
use MetaFox\Music\Http\Resources\v1\Genre\GenreEmbedCollection;
use MetaFox\Music\Models\Song as Model;
use MetaFox\Music\Support\Browse\Traits\Song\ExtraTrait;
use MetaFox\Music\Support\Browse\Traits\Song\StatisticTrait;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class SongItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class SongItem extends JsonResource
{
    use StatisticTrait;
    use ExtraTrait;
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
        $album = $this->resource->album ?? null;

        if (null !== $album) {
            $album = new AlbumDetail($album);
        }
        $text = parse_output()->getDescription($this->getShortDescription());

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->moduleName(),
            'resource_name'     => $this->resource->entityType(),
            'name'              => $this->resource->name,
            'file_name'         => $this->resource->original_name,
            'description'       => htmlspecialchars_decode($text),
            'is_featured'       => (bool) $this->resource->is_featured,
            'is_sponsored'      => (bool) $this->resource->is_sponsor,
            'is_approved'       => (bool) $this->resource->isApproved(),
            'duration'          => $this->resource->duration,
            'image'             => [
                'url'       => $this->resource->image,
                'file_type' => 'image/*',
            ],
            'statistic'         => $this->getStatistic(),
            'link'              => $this->resource->toLink(),
            'destination'       => $this->resource->link_media_file,
            'url'               => $this->resource->toUrl(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'genres'            => new GenreEmbedCollection($this->resource->genres),
            'creation_date'     => $this->resource->created_at,
            'extra'             => $this->getExtra(),
            'modification_date' => $this->resource->updated_at,
            'album'             => $album,
        ];
    }
}
