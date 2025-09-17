<?php

namespace MetaFox\Photo\Contracts;

use Illuminate\Support\Collection;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Album as ModelsAlbum;
use MetaFox\Platform\Contracts\User;

interface AlbumContract
{
    /**
     * @return array
     */
    public function getDefaultTypes(): array;

    /**
     * @param  int|null $value
     * @return bool
     */
    public function isDefaultAlbum(?int $value): bool;

    /**
     * @param Album $album
     *
     * @return string
     */
    public function getDefaultAlbumTitle(Album $album): string;

    /**
     * @param  User   $context
     * @param  string $userType
     * @param  int    $userId
     * @return mixed
     */
    public function chunkingTrashedAlbums(User $context, string $userType, int $userId);

    /**
     * @param ModelsAlbum $album
     * @param int|null    $limit
     * @param bool        $forApproved
     * @return Collection|null
     */
    public function getMediaItems(ModelsAlbum $album, ?int $limit = 4, bool $forApproved = true): ?Collection;
}
