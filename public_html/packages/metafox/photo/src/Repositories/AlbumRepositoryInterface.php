<?php

namespace MetaFox\Photo\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\Contracts\MediaAlbumRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Album.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface AlbumRepositoryInterface extends HasSponsor, HasFeature, MediaAlbumRepositoryInterface, HasSponsorInFeed
{
    /**
     * @return string
     */
    public function model(): string;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return mixed
     * @throws AuthorizationException
     */
    public function viewAlbum(User $context, int $id);

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewAlbums(User $context, User $owner, array $attributes = []): Paginator;

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Album
     * @throws AuthorizationException
     */
    public function createAlbum(User $context, User $owner, array $attributes): Album;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Album
     * @throws AuthorizationException
     */
    public function updateAlbum(User $context, int $id, array $attributes): Album;

    /**
     * @param User  $context
     * @param int   $id
     * @param array $attributes
     * @return array
     */
    public function uploadMedias(User $context, int $id, array $attributes): array;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return mixed
     */
    public function deleteAlbum(User $context, int $id);

    /**
     * @param int $limit
     *
     * @return Paginator
     */
    public function findFeature(int $limit = 4): Paginator;

    /**
     * @param int $limit
     *
     * @return Paginator
     */
    public function findSponsor(int $limit = 4): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param Content $model
     *
     * @return bool
     */
    public function isPending(Content $model): bool;

    /**
     * @param Album $album
     * @param int   $photoId
     */
    public function updateAlbumCover(Album $album, int $photoId = 0): void;

    /**
     * @param User $context
     * @param User $owner
     * @return array<int, mixed>
     */
    public function getAlbumsForForm(User $context, User $owner): array;

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
     * @param int   $ownerId
     * @param array $types
     * @return Collection
     */
    public function getDefaultUserAlbums(int $ownerId, array $types = []): Collection;

    /**
     * @param int $id
     * @param int $ownerId
     * @return bool
     */
    public function isDefaultUserAlbum(int $id, int $ownerId = 0): bool;

    /**
     * @param int $id
     * @return Album|null
     */
    public function getAlbumById(int $id): ?Album;

    /**
     * @param Album $album
     * @param Photo $photo
     * @return void
     */
    public function removeAvatarFromAlbum(Album $album, Photo $photo): void;

    /**
     * @param Album $album
     * @param Photo $photo
     * @return void
     */
    public function removeCoverFromAlbum(Album $album, Photo $photo): void;
}
