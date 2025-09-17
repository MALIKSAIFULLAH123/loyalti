<?php

namespace Metafox\Authorization;

use Illuminate\Support\Facades\Facade;

class Authorization extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'authorization';
    }
}
