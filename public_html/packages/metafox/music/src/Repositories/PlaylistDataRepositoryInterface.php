<?php

namespace MetaFox\Music\Repositories;

use MetaFox\Music\Models\PlaylistData;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as ContractUser;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface PlaylistDataRepositoryInterface.
 * @mixin BaseRepository
 */
interface PlaylistDataRepositoryInterface
{
    /**
     * @param  int               $playlistId
     * @param  int               $itemId
     * @return PlaylistData|null
     */
    public function findPlaylistData(int $playlistId, int $itemId): ?PlaylistData;

    /**
     * @param  ContractUser $user
     * @param  int          $songId
     * @return array
     */
    public function getPlaylistIdsBySong(User $user, int $songId): array;
}
