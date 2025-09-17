<?php

namespace MetaFox\Photo\Listeners;

use Exception;
use Illuminate\Support\Arr;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\TempFileModel;
use MetaFox\Platform\Contracts\User;

class MediaUploadListener
{
    /** @var PhotoRepositoryInterface */
    private $repository;

    public function __construct(PhotoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Photo set feed content won't apply to every single photo. DO NOT assign content to per photo.
     *
     * @param  User                 $user
     * @param  User                 $owner
     * @param  string               $itemType
     * @param  TempFileModel        $file
     * @param  array<string, mixed> $params
     * @return Content|null
     * @throws Exception
     */
    public function handle(User $user, User $owner, string $itemType, TempFileModel $file, array $params): ?Content
    {
        if (Photo::ENTITY_TYPE != $itemType) {
            return null;
        }

        $albumId = Arr::get($params, 'album_id', 0);

        if ($albumId > 0) {
            $album = app('events')->dispatch('photo.album.get_by_id', [$albumId], true);

            if (null == $album) {
                return null;
            }

            if (!policy_check(PhotoPolicy::class, 'uploadToAlbum', $user, $album->owner, $albumId)) {
                return null;
            }
        }

        return $this->repository->tempFileToPhoto($user, $owner, $file, $params);
    }
}
