<?php

namespace MetaFox\Friend\Support\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Friend\Support\Friend as Service;
use MetaFox\Platform\Contracts\User;

/**
 * Class Friend Facade.
 *
 * @method static int        getFriendship(User $user, User $owner)
 * @method static bool       isFriend(User $user, User $owner)
 * @method static Collection getUsersForMention(array $ids)
 *
 * @see Service
 */
class Friend extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Service::class;
    }
}
