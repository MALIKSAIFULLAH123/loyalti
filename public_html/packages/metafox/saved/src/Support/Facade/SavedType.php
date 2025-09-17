<?php

namespace MetaFox\Saved\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Saved\Contracts\Support\SavedTypeContract;

/**
 * Class SavedType.
 *
 * @method static array getFilterOptions()
 * @method static array transformItemType()
 * @ignore
 * @codeCoverageIgnore
 */
class SavedType extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SavedTypeContract::class;
    }
}
