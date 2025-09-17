<?php
namespace MetaFox\Photo\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Photo\Contracts\PhotoGroupSupportContract;

/**
 * @method static array getMediaItems(\MetaFox\Photo\Models\PhotoGroup $group, bool $isLoadForEdit = false, ?int $limit = 4)
 */
class PhotoGroup extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PhotoGroupSupportContract::class;
    }
}
