<?php

namespace MetaFox\Music\Repositories;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Music\Models\Playlist;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Music\Models\Song as Model;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;

/**
 * Interface SongRepositoryInterface.
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 *
 * @mixin CollectTotalItemStatTrait
 * @mixin BaseRepository
 * @mixin UserMorphTrait
 */
interface SongRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * View songs.
     *
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewSongs(ContractUser $context, ContractUser $owner, array $attributes): Paginator;

    /**
     * Create a song.
     *
     * @param  ContractUser         $context
     * @param  ContractUser         $owner
     * @param  array<string, mixed> $attributes
     * @return Model
     * @throws Exception
     * @see StoreBlockLayoutRequest
     */
    public function createSong(ContractUser $context, ContractUser $owner, array $attributes): Model;

    /**
     * Update a song.
     *
     * @param  ContractUser            $context
     * @param  int                     $id
     * @param  array<string, mixed>    $attributes
     * @return Model
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updateSong(ContractUser $context, int $id, array $attributes): Model;

    /**
     * View a song.
     *
     * @param ContractUser $context
     * @param int          $id
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function viewSong(ContractUser $context, int $id): Model;

    /**
     * Delete a song.
     *
     * @param ContractUser $context
     * @param int          $id
     *
     * @return bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function deleteSong(ContractUser $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Model
     * @throws Exception
     * @throws AuthorizationException
     */
    public function downloadSong(ContractUser $context, int $id): Model;

    /**
     * @param  Model $song
     * @return bool
     */
    public function updateTotalPlay(Model $song): bool;

    /**
     * @param  Model    $song
     * @param  Playlist $playlist
     * @return bool
     */
    public function removeFromPlaylist(Model $song, Playlist $playlist): bool;

    /**
     * @param  int $songFileId
     * @return int
     */
    public function getSongDuration(int $songFileId): int;
}
