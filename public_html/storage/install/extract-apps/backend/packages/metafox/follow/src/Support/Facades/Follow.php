<?php

namespace MetaFox\Follow\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Follow\Support\Follow as Service;

/**
 * Class Follow Facade.
 *
 * @see Service
 */
class Follow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Service::class;
    }
}
