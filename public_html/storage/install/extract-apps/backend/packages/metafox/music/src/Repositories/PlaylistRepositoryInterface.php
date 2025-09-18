<?php

namespace MetaFox\Music\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Playlist as Model;
use MetaFox\Music\Models\PlaylistData;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;

/**
 * Interface PlaylistRepositoryInterface.
 * @mixin BaseRepository
 * @mixin UserMorphTrait
 * @mixin CollectTotalItemStatTrait
 */
interface PlaylistRepositoryInterface
{
    /**
     * View playlist.
     *
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewPlaylists(ContractUser $context, ContractUser $owner, array $attributes): Paginator;

    /**
     * Create a playlist.
     *
     * @param  ContractUser         $context
     * @param  ContractUser         $owner
     * @param  array<string, mixed> $attributes
     * @return Model
     * @throws \Exception
     * @see StoreBlockLayoutRequest
     */
    public function createPlaylist(ContractUser $context, ContractUser $owner, array $attributes): Model;

    /**
     * Update a playlist.
     *
     * @param  ContractUser            $context
     * @param  int                     $id
     * @param  array<string, mixed>    $attributes
     * @return Playlist
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updatePlaylist(ContractUser $context, int $id, array $attributes): Model;

    /**
     * View a playlist.
     *
     * @param ContractUser $context
     * @param int          $id
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function viewPlaylist(ContractUser $context, int $id): Model;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewPlaylistItems(ContractUser $context, int $id, array $attributes = []): Paginator;

    /**
     * Delete a playlist.
     *
     * @param ContractUser $context
     * @param int          $id
     *
     * @return bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function deletePlaylist(ContractUser $context, int $id): bool;

    /**
     * @param  ContractUser           $context
     * @param  int                    $playlistId
     * @param  int                    $songId
     * @return PlaylistData
     * @throws AuthorizationException
     */
    public function addSong(ContractUser $context, int $playlistId, int $songId): PlaylistData;

    /**
     * @param ContractUser $context
     *
     * @return array
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function getPlaylistOptions(ContractUser $context): array;

    /**
     * @param ContractUser $context
     * @param int          $itemId
     *
     * @return Collection
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function getPlaylistByItemId(ContractUser $context, int $itemId): Collection;
}
