<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Repositories\PhotoRepositoryInterface;

class UpdateAvatarPathListener
{
    /**
     * @param int    $photoId
     * @param string $path
     * @param int[]  $sizes
     * @param int[]  $squareSizes
     *
     * @return string
     */
    public function handle(int $photoId, string $path, array $sizes, array $squareSizes = []): string
    {
        return resolve(PhotoRepositoryInterface::class)->updateAvatarPath($photoId, $path, $sizes, $squareSizes);
    }
}
