<?php

namespace MetaFox\Core\Support\Facades;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Core\Contracts\AttachmentFileTypeContract;

/**
 * class Country.
 * @method static void       clearCache()
 * @method static Collection getAllActive()
 * @method static Collection getAttachmentFileTypes()
 * @method static array      getAllMineTypeActive()
 * @method static array      getAllExtensionActive()
 * @see \MetaFox\Core\Support\AttachmentFileType
 */
class AttachmentFileType extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AttachmentFileTypeContract::class;
    }
}
