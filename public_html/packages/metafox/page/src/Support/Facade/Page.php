<?php

namespace MetaFox\Page\Support\Facade;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Page\Contracts\PageContract;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Platform\Contracts\User;

/**
 * @method static array      getMentions(string $content)
 * @method static Collection getPagesForMention(array $ids)
 * @method static Builder    getPageBuilder(User $user)
 * @method static array      getListTypes()
 * @method static array      getAllowApiRules()
 * @method static array      getInfoSettingsSupportByResolution(string $resolution)
 * @method static bool       isFollowing(User $context, User $user)
 * @method static bool       getProfileMenuSettings(\MetaFox\Page\Models\Page $page)
 * @method static Builder    getMemberBuilderForLoginAsPage(Model $page)
 * @method static bool       allowHtmlOnDescription()
 * @method static string     getCacheKeyDefaultTabActive(\MetaFox\Page\Models\Page $page)
 * @method static string     getDefaultTabMenu(User $user, \MetaFox\Page\Models\Page $page)
 */
class Page extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PageContract::class;
    }
}
