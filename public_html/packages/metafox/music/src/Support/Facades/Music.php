<?php

namespace MetaFox\Music\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Music\Contracts\SupportInterface;

/**
 * @method static array  getEntityTypeOptions()
 * @method static string getDefaultSearchEntityType()
 * @method static string convertEntityType(string $entityType)
 * @method static array  getSongSortOptions()
 * @method static array  getDefaultSortOptions()
 */
class Music extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SupportInterface::class;
    }
}
