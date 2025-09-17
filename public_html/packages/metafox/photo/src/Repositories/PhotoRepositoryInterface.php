<?php

namespace MetaFox\Photo\Repositories;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\TagFriendModel;
use MetaFox\Platform\Contracts\TempFileModel;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface PhotoRepositoryInterface.
 * @mixin BaseRepository
 * @method Photo getModel()
 * @method Photo find($id, $columns = ['*'])
 * @method Photo newModelInstance()
 *
 * @mixin CollectTotalItemStatTrait
 */
interface PhotoRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     * @param int                  $albumType
     * @param string|null          $typeId
     *
     * @return int[]
     * @throws AuthorizationException
     * @throws Exception
     */
    public function createPhoto(
        User    $context,
        User    $owner,
        array   $attributes,
        int     $albumType = Album::TIMELINE_ALBUM,
        ?string $typeId = null
    ): array;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Photo
     * @throws Exception
     * @throws AuthorizationException
     */
    public function updatePhoto(User $context, int $id, array $attributes): Photo;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Photo
     * @throws AuthorizationException
     */
    public function viewPhoto(User $context, int $id): Photo;

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewPhotos(User $context, User $owner, array $attributes = []): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return array<string, mixed>
     */
    public function deletePhoto(User $context, int $id): array;

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
     * @param User $user
     * @param User $owner
     * @param int  $albumType
     *
     * @return Album
     * @see Album - check album type in album model.
     */
    public function getAlbum(User $user, User $owner, int $albumType): Album;

    /**
     * @param int                  $albumId
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public function getPrivacyFromAlbum(int $albumId, array $attributes): array;

    /**
     * @param User                 $user
     * @param User                 $owner
     * @param TempFileModel        $tempFile
     * @param array<string, mixed> $params
     *
     * @return Photo
     * @throws Exception
     */
    public function tempFileToPhoto(User $user, User $owner, TempFileModel $tempFile, array $params = []): Photo;

    /**
     * @param User                 $user
     * @param User                 $owner
     * @param Photo                $photo
     * @param TempFileModel        $tempFile
     * @param array<string, mixed> $params
     *
     * @return Photo
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function tempFileToExistPhoto(
        User          $user,
        User          $owner,
        Photo         $photo,
        TempFileModel $tempFile,
        array         $params = []
    ): Photo;

    /**
     * @param User                 $context
     * @param int                  $feedId
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function getPhotoByFeedId(User $context, int $feedId, array $attributes = []): Paginator;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function makeProfileCover(User $context, int $id, array $attributes = []): bool;

    /**
     * @param User        $context
     * @param int         $id
     * @param string|null $imageCrop
     *
     * @return array<string, mixed>|null
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function makeProfileAvatar(User $context, int $id, ?string $imageCrop = null): ?array;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function makeParentCover(User $context, int $id): bool;

    /**
     * @param User $user
     * @param int  $id
     *
     * @return PhotoGroup
     * @throws AuthorizationException
     */
    public function viewPhotoSet(User $user, int $id): PhotoGroup;

    /**
     * @param User $context
     * @param int  $photoId
     *
     * @return Collection
     * @throws AuthorizationException
     */
    public function getTaggedFriends(User $context, int $photoId): Collection;

    /**
     * @param User  $user
     * @param User  $friend
     * @param int   $photoId
     * @param float $pxValue
     * @param float $pyValue
     *
     * @return TagFriendModel
     * @throws AuthorizationException
     */
    public function tagFriend(User $user, User $friend, int $photoId, float $pxValue, float $pyValue): ?TagFriendModel;

    /**
     * @param User $user
     * @param int  $tagId
     *
     * @return false|int
     * @throws AuthorizationException
     */
    public function deleteTaggedFriend(User $user, int $tagId);

    /**
     * @param int    $photoId
     * @param string $path
     * @param int[]  $sizes
     * @param int[]  $squareSizes
     *
     * @return string
     */
    public function updateAvatarPath(int $photoId, string $path, array $sizes = [], array $squareSizes = []): string;

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     * @return PhotoGroup
     */
    public function uploadMedias(User $context, User $owner, array $attributes): PhotoGroup;

    /**
     * @param int $groupId
     * @return Collection|null
     */
    public function getPhotosByGroupId(int $groupId): ?Collection;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Photo
     * @throws Exception
     * @throws AuthorizationException
     */
    public function downloadPhoto(User $context, int $id): Photo;

    /**
     * @param User     $context
     * @param int      $id
     * @param  ?User   $owner
     * @param  ?string $imageCrop
     * @return array<string, mixed>|null
     */
    public function makeParentAvatar(User $context, int $id, ?User $owner = null, ?string $imageCrop = null): ?array;

    /**
     * @param Photo $photo
     * @return void
     */
    public function cleanUpRelationData(Photo $photo): void;
}
