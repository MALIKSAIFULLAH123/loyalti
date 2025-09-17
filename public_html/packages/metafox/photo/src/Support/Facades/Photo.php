<?php

namespace MetaFox\Photo\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo as PhotoModel;
use MetaFox\Platform\Contracts\User;

/**
 * Class Photo.
 * @method static int[] createPhoto(User $context, User $owner, array $attributes, int $contextType = Album::TIMELINE_ALBUM): array
 * @method static bool       isVideoAllow()
 * @method static null|array getImages(User $context, PhotoModel $photo)
 * @method static array      getAllowMatureContent()
 * @method static array      getMatureContentOptions()
 * @method static array      transformDataForFeed(array $params)
 * @see     \MetaFox\Photo\Support\Photo
 * @see     \MetaFox\Photo\Models\Album - check album type in album model.
 */
class Photo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Photo';
    }
}
