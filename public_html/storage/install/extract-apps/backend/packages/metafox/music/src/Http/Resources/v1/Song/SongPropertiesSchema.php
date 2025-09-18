<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Http\Resources\v1\Album\AlbumPropertiesSchema;
use MetaFox\Music\Models\Song;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class SongPropertiesSchema.
 * @property ?Song $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SongPropertiesSchema extends JsonResource
{
    use HasDescriptionTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $user           = new UserPropertiesSchema($this->resource?->user);
        $userProperties = Arr::dot($user->toArray($request), 'user_');
        $userProperties = Arr::undot($userProperties);

        $album           = new AlbumPropertiesSchema($this->resource?->album);
        $albumProperties = Arr::dot($album->toArray($request), 'album_');
        $albumProperties = Arr::undot($albumProperties);

        if (!$this->resource instanceof Song) {
            return array_merge($this->resourcesDefault(), $userProperties, $albumProperties);
        }

        $reactItem = $this->resource->reactItem();

        return array_merge([
            'id'                => $this->resource->entityId(),
            'name'              => ban_word()->clean($this->resource->name),
            'file_name'         => $this->resource->original_name,
            'description'       => $this->getShortDescription(),
            'text'              => $this->getDescription(),
            'duration_iso'      => CarbonInterval::seconds($this->resource->duration)->spec(),
            'duration'          => $this->resource->duration,
            'image'             => $this->resource->image,
            'images'            => is_array($this->resource->images) ? array_values($this->resource->images) : null,
            'link'              => $this->resource->toLink(),
            'destination'       => $this->resource->link_media_file,
            'url'               => $this->resource->toUrl(),
            'creation_date'     => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date' => Carbon::parse($this->resource->updated_at)->format('c'),
            'total_like'        => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_share'       => $this->resource->total_share,
            'total_view'        => $this->resource->total_view,
            'total_play'        => $this->resource->total_play,
            'total_score'       => $this->resource->total_score,
            'total_download'    => $this->resource->total_download,
            'genre_names'       => $this->resource->genres->pluck('name')->toArray(),
        ], $userProperties, $albumProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                => null,
            'name'              => null,
            'file_name'         => null,
            'description'       => null,
            'text'              => null,
            'duration_iso'      => null,
            'duration'          => null,
            'image'             => null,
            'images'            => null,
            'link'              => null,
            'destination'       => null,
            'url'               => null,
            'creation_date'     => null,
            'modification_date' => null,
            'total_like'        => null,
            'total_share'       => null,
            'total_view'        => null,
            'total_play'        => null,
            'total_score'       => null,
            'total_download'    => null,
            'genre_names'       => null,
        ];
    }
}
