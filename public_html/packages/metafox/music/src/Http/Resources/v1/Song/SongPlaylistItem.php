<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Music\Http\Resources\v1\Album\AlbumDetail;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Repositories\PlaylistDataRepositoryInterface;

/**
 * Class SongItem.
 * @property Song $resource
 */
class SongPlaylistItem extends SongPlayItem
{
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

        $data = parent::toArray($request);

        return array_merge($data, [
            'playlist_ids' => resolve(PlaylistDataRepositoryInterface::class)->getPlaylistIdsBySong($context, $this->resource->entityId()),
        ]);
    }
}
