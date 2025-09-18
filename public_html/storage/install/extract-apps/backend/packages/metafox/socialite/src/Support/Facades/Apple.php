<?php

namespace MetaFox\Socialite\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Socialite\Support\Apple as SupportApple;

/**
 * Class Provider.
 *
 * @method static array<string, mixed> generateClientSecret(array $params = [])
 * @see \MetaFox\Socialite\Support\Apple
 */
class Apple extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupportApple::class;
    }
}
