<?php

namespace MetaFox\Music\Repositories\Eloquent;

use Illuminate\Database\Query\JoinClause;
use MetaFox\Music\Models\PlaylistData;
use MetaFox\Music\Repositories\PlaylistDataRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class PlaylistDataRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class PlaylistDataRepository extends AbstractRepository implements PlaylistDataRepositoryInterface
{
    public function model()
    {
        return PlaylistData::class;
    }

    public function findPlaylistData(int $playlistId, int $itemId): ?PlaylistData
    {
        return $this->getModel()->newQuery()
            ->where(['playlist_id' => $playlistId, 'item_id' => $itemId])
            ->first();
    }

    public function getPlaylistIdsBySong(User $user, int $songId): array
    {
        return $this->getModel()->newQuery()
            ->join('music_playlists', function (JoinClause $joinClause) use ($user) {
                $joinClause->on('music_playlists.id', '=', 'music_playlist_data.playlist_id')
                    ->where('music_playlists.user_id', '=', $user->entityId());
            })
            ->where('music_playlist_data.item_id', '=', $songId)
            ->select(['music_playlist_data.playlist_id'])
            ->get()
            ->pluck('playlist_id')
            ->toArray();
    }
}
