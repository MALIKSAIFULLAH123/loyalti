<?php

namespace MetaFox\Platform\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void end(string $label)
 * @method static void tick(string $label)
 * @method static void log(string $label, array $context =[])
 * @method static void dump()
 */
class Profiling extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \MetaFox\Platform\Support\PhpProfiling::class;
    }
}
