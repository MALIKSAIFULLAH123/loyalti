<?php

namespace MetaFox\Photo\Support\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Photo\Contracts\AlbumContract;
use MetaFox\Photo\Models\Album as ModelsAlbum;
use MetaFox\Platform\Contracts\User;

/**
 * @method static bool            isDefaultAlbum(int $value)
 * @method static void            chunkingTrashedAlbums(User $context, string $userType, int $userId)
 * @method        static          getDefaultAlbumTitle(mixed $resource)
 * @method static Collection|null getMediaItems(ModelsAlbum $album, ?int $limit = 4, bool $forApproved = true)
 */
class Album extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AlbumContract::class;
    }
}
