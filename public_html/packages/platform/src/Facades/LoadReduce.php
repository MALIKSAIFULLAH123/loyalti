<?php

namespace MetaFox\Platform\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\LoadReduce\Reducer;

/**
 * @method static void   with(array $parameters)
 * @method static void   register(array $reducers)
 * @method static mixed  get(string $key, mixed $callback = null)
 * @method static mixed  remember(string $key, \Closure $callback = null)
 * @method static void   put(string $key, mixed $value)
 * @method static void   putMany(array $values)
 * @method static void   flush()
 * @method static mixed  getEntity(string $string, int $id, \Closure $param)
 * @method static void   capture(mixed $data)
 * @method static void   disable()
 * @method static void   enable()
 * @method        static setLogger(\Psr\Log\LoggerInterface $build)
 */
class LoadReduce extends Facade
{
    protected static function getFacadeAccessor()
    {
        /* @see Reducer */
        return 'reducer';
    }
}
