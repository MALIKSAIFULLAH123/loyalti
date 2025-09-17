<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;

class MediaRemoveListener
{
    private PhotoRepositoryInterface $repository;

    public function __construct(PhotoRepositoryInterface $repository)
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
        if (Photo::ENTITY_TYPE != $type) {
            return null;
        }

        return $this->repository->find($id)->delete();
    }
}
