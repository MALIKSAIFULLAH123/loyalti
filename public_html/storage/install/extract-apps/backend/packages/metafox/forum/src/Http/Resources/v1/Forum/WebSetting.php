<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Forum\Http\Resources\v1\Forum;

use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewScope;
use MetaFox\Forum\Support\Facades\Forum as ForumFacade;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
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
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->pageUrl('forum/search')
            ->placeholder(__p('forum::web.search_discussions'))
            ->pageParams([
                'item_type'   => ForumSupport::SEARCH_BY_THREAD,
                'view'        => Browse::VIEW_SEARCH,
                'sort_thread' => ThreadSortScope::SORT_LATEST_DISCUSSED,
                'sort_post'   => Browse::SORT_RECENT,
            ]);

        $this->add('viewAll')
            ->apiUrl('forum')
            ->apiRules([
                'q'    => ['truthy', 'q'],
                'view' => ['includes', 'view', ThreadViewScope::getAllowView()],
                'sort' => [
                    'or',
                    [
                        'and',
                        ['eq', 'item_type', ForumSupport::SEARCH_BY_THREAD],
                        [
                            'includes',
                            'sort',
                            [ThreadSortScope::SORT_LATEST_DISCUSSED, ThreadSortScope::SORT_RECENT_POST, Browse::SORT_MOST_LIKED, Browse::SORT_MOST_DISCUSSED],
                        ],
                    ],
                    [
                        'and',
                        ['eq', 'item_type', ForumSupport::SEARCH_BY_POST],
                        [
                            'includes',
                            'sort',
                            [Browse::SORT_RECENT, Browse::SORT_MOST_LIKED],
                        ],
                    ],
                ],
                'sort_post' => [
                    'includes',
                    'sort_post',
                    [Browse::SORT_RECENT, Browse::SORT_MOST_LIKED],
                ],
                'sort_thread' => [
                    'includes',
                    'sort_thread',
                    [ThreadSortScope::SORT_LATEST_DISCUSSED, ThreadSortScope::SORT_RECENT_POST, Browse::SORT_MOST_LIKED, Browse::SORT_MOST_DISCUSSED],
                ],
                'sort_type' => ['includes', 'sort_type', SortScope::getAllowSortType()],
                'when'      => ['includes', 'when', WhenScope::getAllowWhen()],
                'item_type' => ['includes', 'item_type', ForumFacade::getItemTypesForSearch()],
                'forum_id'  => ['truthy', 'forum_id'],
            ]);

        $this->add('homePage')
            ->pageUrl('forum');

        $this->add('viewItem')
            ->pageUrl('forum/:id')
            ->apiUrl('forum/:id');

        $this->add('viewSubForum')
            ->apiUrl('forum-subs/:id');

        $this->add('viewQuickNavigationItems')
            ->apiUrl('forum')
            ->apiParams([
                'view' => ForumSupport::VIEW_QUICK_NAVIGATION,
            ]);
    }
}
