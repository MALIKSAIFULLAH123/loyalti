<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;

class PhotoCreateListener
{
    public function __construct(protected PhotoRepositoryInterface $photoRepository) { }

    /**
     * @param mixed $data
     *
     * @return Collection
     * @throws AuthorizationException
     */
    public function handle(...$data): Collection
    {
        $photoIds = $this->photoRepository->createPhoto(...$data);
        $photo    = Photo::query()->whereIn('id', $photoIds)->get();

        return collect($photo);
    }
}
