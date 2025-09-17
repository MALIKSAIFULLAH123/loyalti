<?php

namespace MetaFox\Platform\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void onTerminated($callback)
 * @method static void handleTerminated()
 */
class RequestLifecycle extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \MetaFox\Platform\Support\RequestLifecycleHandler::class;
    }
}
