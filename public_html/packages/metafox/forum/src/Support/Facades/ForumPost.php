<?php

namespace MetaFox\Forum\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Forum\Contracts\ForumPostSupportContract;
use MetaFox\Platform\Contracts\User;

/**
 * @method static bool deletePost(User $context, int $id)
 * @method static array getCustomExtra(User $user, ForumPost $model)
 * @method static array getRelations()
 */
class ForumPost extends Facade
{
    public static function getFacadeAccessor()
    {
        return ForumPostSupportContract::class;
    }
}
