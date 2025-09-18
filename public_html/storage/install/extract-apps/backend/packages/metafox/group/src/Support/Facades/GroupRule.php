<?php

namespace MetaFox\Group\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Group\Contracts\RuleSupportContract;

/**
 * @method static int getDescriptionMaxLength()
 */
class GroupRule extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RuleSupportContract::class;
    }
}
