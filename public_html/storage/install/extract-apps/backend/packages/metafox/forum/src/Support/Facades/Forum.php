<?php

namespace MetaFox\Forum\Support\Facades;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Forum\Contracts\ForumSupportContract;
use MetaFox\Platform\Contracts\Content;

/**
 * @method static string          getViewCacheId()
 * @method static string          getViewMobileCacheId()
 * @method static string          getFormCacheId()
 * @method static string          getNavigationCacheId()
 * @method static string          getActiveForumIdsCacheId()
 * @method static string          getModuleName()
 * @method static array           getItemTypesForSearch()
 * @method static int             getTotalAllThreads(int $id)
 * @method static int             getTotalAllSubs(int $id)
 * @method static int             getTotalRepliesAllSubs(int $id)
 * @method static int             getOpenStatus()
 * @method static int             getClosedStatus()
 * @method static array           buildForumIdsForSearch(int $id)
 * @method static array           buildForumsForForm(int $parentId = 0)
 * @method static array           buildForumsForView(int $parentId = 0)
 * @method static array           buildForumsForViewMobile()
 * @method static int             buildTotalThreadsForNavigation(int $id, int $total = 0)
 * @method static int             buildTotalSubsForNavigation(int $id)
 * @method static Collection|null buildForumsForNavigation(int $parentId = 0, array $attributes = [])
 * @method static void            updateAttachments(Content $item, ?array $attachments = [])
 */
class Forum extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ForumSupportContract::class;
    }
}
