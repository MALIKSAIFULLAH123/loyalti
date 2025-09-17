<?php

namespace MetaFox\Photo\Listeners;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Contracts\TempFileModel;
use MetaFox\Platform\Contracts\User;

class MediaPatchUpdateListener
{
    private PhotoRepositoryInterface $repository;

    public function __construct(PhotoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Photo set feed content won't apply to every single photo. DO NOT assign content to per photo.
     *
     * @param  User                   $user
     * @param  User                   $owner
     * @param  TempFileModel          $file
     * @param  array<string, mixed>   $params
     * @param  Media                  $media
     * @return Content|null
     * @throws AuthorizationException
     * @throws Exception
     */
    public function handle(User $user, string $itemType, int $itemId, array $attributes): ?bool
    {
        if (Photo::ENTITY_TYPE != $itemType) {
            return null;
        }

        $this->repository->updatePhoto($user, $itemId, $attributes);

        return true;
    }
}
