<?php

namespace MetaFox\Music\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Music\Models\Album as Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;

/**
 * Interface AlbumRepositoryInterface.
 * @mixin BaseRepository
 * @mixin UserMorphTrait
 * @mixin CollectTotalItemStatTrait
 */
interface AlbumRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * View albums.
     *
     * @param ContractUser         $context
     * @param ContractUser         $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewAlbums(ContractUser $context, ContractUser $owner, array $attributes): Paginator;

    /**
     * Create a album.
     *
     * @param  ContractUser         $context
     * @param  ContractUser         $owner
     * @param  array<string, mixed> $attributes
     * @return Model
     * @see StoreBlockLayoutRequest
     */
    public function createAlbum(ContractUser $context, ContractUser $owner, array $attributes): Model;

    /**
     * Update a album.
     *
     * @param  ContractUser            $context
     * @param  int                     $id
     * @param  array<string, mixed>    $attributes
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updateAlbum(ContractUser $context, int $id, array $attributes): Model;

    /**
     * View a album.
     *
     * @param ContractUser $context
     * @param int          $id
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function viewAlbum(ContractUser $context, int $id): Model;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewAlbumItems(User $context, int $id, array $attributes = []): Paginator;

    /**
     * Delete a album.
     *
     * @param ContractUser $context
     * @param int          $id
     *
     * @return bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function deleteAlbum(ContractUser $context, int $id): bool;
}
