<?php

namespace MetaFox\Chat\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Chat\Contracts\ChatContract;
use MetaFox\Chat\Support\Chat as ChatSupport;

/**
 * @method static disableChat(string $package, bool $optimizeClear = true);
 * @see ChatSupport
 */
class Chat extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ChatContract::class;
    }
}
