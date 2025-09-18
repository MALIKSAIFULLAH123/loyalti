<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\VideoRepositoryInterface;

class MediaRemoveListener
{
    private VideoRepositoryInterface $repository;

    public function __construct(VideoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Photo set feed content won't apply to every single photo. DO NOT assign content to per photo.
     *
     * @param  int      $id
     * @param  string   $type
     * @return int|null
     */
    public function handle(int $id, string $type): ?int
    {
        if (Video::ENTITY_TYPE != $type) {
            return null;
        }

        return $this->repository->delete($id);
    }
}
