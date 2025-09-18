<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Contracts\TempFileModel;
use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\VideoRepositoryInterface;

class MediaUpdateListener
{
    private VideoRepositoryInterface $repository;

    public function __construct(VideoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User                 $user
     * @param  User                 $owner
     * @param  TempFileModel        $file
     * @param  array<string, mixed> $params
     * @param  Media                $media
     * @return Content|null
     */
    public function handle(User $user, User $owner, Media $media, TempFileModel $file, array $params): ?Content
    {
        if (!$media instanceof Video) {
            return null;
        }

        return $this->repository->tempFileToExistVideo($user, $owner, $media, $file, $params);
    }
}
