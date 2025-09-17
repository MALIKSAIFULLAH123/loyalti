<?php

namespace MetaFox\Forum\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Forum\Contracts\ForumThreadSupportContract;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * @method static int getDefaultMinimumTitleLength()
 * @method static int getDefaultMaximumTitleLength()
 * @method static array getCustomPolicies(User $user, Content $resource)
 * @method static bool canDisplayOnWiki(User $user)
 * @method static bool checkCreateThreads(User $user)
 * @method static array getRelations()
 * @method static ?array getIntegratedItem(User $user, User $owner, ?Entity $entity = null, string $resolution = 'web')
 * @method static ForumThread|null getThread(int $id)
 */
class ForumThread extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ForumThreadSupportContract::class;
    }
}
