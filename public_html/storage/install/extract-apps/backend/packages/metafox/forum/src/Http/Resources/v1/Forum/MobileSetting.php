<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Forum\Http\Resources\v1\Forum;

use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewScope;
use MetaFox\Forum\Support\Facades\Forum as ForumFacade;
use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;

/**
 *--------------------------------------------------------------------------
 * Forum Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->apiUrl('forum')
            ->placeholder(__p('forum::web.search_discussions'))
            ->apiParams([
                'q'           => ':q',
                'sort_thread' => ':sort_thread',
                'sort_post'   => ':sort_post',
                'when'        => ':when',
                'forum_id'    => ':forum_id',
                'view'        => Browse::VIEW_SEARCH,
                'item_type'   => ':item_type',
            ]);

        $this->add('viewAll')
            ->apiUrl('forum')
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'view'        => ['includes', 'view', ThreadViewScope::getAllowView()],
                'sort'        => ['includes', 'sort', SortScope::getAllowSort()],
                'sort_thread' => ['includes', 'sort_thread', ThreadSortScope::getAllowSort()],
                'sort_post'   => ['includes', 'sort_post', [Browse::SORT_RECENT, Browse::SORT_MOST_LIKED]],
                'sort_type'   => ['includes', 'sort_type', SortScope::getAllowSortType()],
                'when'        => ['includes', 'when', WhenScope::getAllowWhen()],
                'item_type'   => ['includes', 'item_type', ForumFacade::getItemTypesForSearch()],
                'forum_id'    => ['truthy', 'forum_id'],
            ])
            ->apiParams([
                'q'           => ':q',
                'sort_thread' => ':sort_thread',
                'sort_post'   => ':sort_post',
                'when'        => ':when',
                'forum_id'    => ':forum_id',
                'view'        => ':view',
                'item_type'   => ':item_type',
                'sort_type'   => ':sort_type',
            ]);

        $this->add('homePage')
            ->pageUrl('forum');

        $this->add('viewItem')
            ->pageUrl('forum/:id')
            ->apiUrl('forum/:id');
    }
}
