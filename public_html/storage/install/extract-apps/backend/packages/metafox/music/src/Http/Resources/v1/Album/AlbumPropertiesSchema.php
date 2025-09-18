<?php

namespace MetaFox\Music\Http\Resources\v1\Album;

use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Song;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class AlbumPropertiesSchema.
 * @property ?Album $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AlbumPropertiesSchema extends JsonResource
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

        if (!$this->resource instanceof Album) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        $reactItem = $this->resource->reactItem();

        return array_merge([
            'id'               => $this->resource->entityId(),
            'name'             => ban_word()->clean($this->resource->name),
            'description'      => $this->getShortDescription(['albumText' => 'text_parsed']),
            'text'             => $this->getDescription(['albumText' => 'text_parsed']),
            'image'            => $this->resource->image,
            'images'           => is_array($this->resource->images) ? array_values($this->resource->images) : null,
            'link'             => $this->resource->toLink(),
            'url'              => $this->resource->toUrl(),
            'creation_date'    => Carbon::parse($this->resource->created_at)->format('c'),
            'year'             => $this->resource->year,
            'total_like'       => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_share'      => $this->resource->total_share,
            'total_view'       => $this->resource->total_view,
            'total_play'       => $this->resource->total_play,
            'total_rating'     => $this->resource->total_rating,
            'total_score'      => $this->resource->total_score,
            'total_song'       => $this->resource->total_track,
            'duration_iso'     => CarbonInterval::seconds($this->resource->total_duration)->spec(),
            'duration'         => $this->resource->total_duration,
            'genre_names'      => $this->resource->genres->pluck('name')->toArray(),
            'structure_tracks' => $this->buildStructureTracks(),
        ], $userProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'               => null,
            'name'             => null,
            'description'      => null,
            'text'             => null,
            'image'            => null,
            'images'           => null,
            'link'             => null,
            'url'              => null,
            'creation_date'    => null,
            'year'             => null,
            'total_like'       => null,
            'total_share'      => null,
            'total_view'       => null,
            'total_play'       => null,
            'total_rating'     => null,
            'total_score'      => null,
            'total_song'       => null,
            'duration_iso'     => null,
            'duration'         => null,
            'genre_names'      => null,
            'structure_tracks' => null,
        ];
    }

    protected function buildStructureTracks(): array
    {
        $result = [
            '@type'           => 'ItemList',
            'numberOfItems'   => $this->resource->total_track,
            'itemListElement' => [],
        ];

        $this->resource->songs()->take(5)->each(function (Song $song, $key) use (&$result) {
            $result['itemListElement'][] = [
                '@type'    => 'ListItem',
                'position' => $key,
                'item'     => [
                    '@type' => 'MusicRecording',
                    'name'  => $song->name,
                ],
            ];
        });

        return $result;
    }
}
