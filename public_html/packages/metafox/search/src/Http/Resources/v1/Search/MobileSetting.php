<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Search\Http\Resources\v1\Search;

use MetaFox\Platform\Resource\MobileSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 *--------------------------------------------------------------------------
 * Search Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('searchGlobalGroup')
            ->apiUrl(apiUrl('search.group.index'))
            ->apiParams([
                'q'          => ':q',
                'is_hashtag' => ':is_hashtag',
                'hashtag'    => ':hashtag',
                'from'       => ':from',
            ]);

        $this->add('viewSuggestions')
            ->apiUrl('search/suggestion')
            ->asGet()
            ->apiParams([
                'q'     => ':q',
                'limit' => 10,
            ]);

        $this->add('hashtagTrending')
            ->apiUrl('search/hashtag/trending')
            ->asGet();

        $this->add('viewSections')
            ->apiUrl('search/group')
            ->apiParams([
                'q'                           => ':q',
                'is_hashtag'                  => ':is_hashtag',
                'hashtag'                     => ':hashtag',
                'when'                        => ':when',
                'owner_id'                    => ':owner_id',
                'sort'                        => ':sort',
                'from'                        => ':from',
                'related_comment_friend_only' => ':related_comment_friend_only',
            ])
            ->asGet()
            ->apiRules([
                'owner_id' => [
                    'truthy',
                    'owner_id',
                ],
                'q' => [
                    'truthy',
                    'q',
                ],
                'is_hashtag' => [
                    'truthy',
                    'is_hashtag',
                ],
                'from' => [
                    'truthy',
                    'from',
                ],
                'hashtag' => [
                    'truthy',
                    'hashtag',
                ],
                'related_comment_friend_only' => [
                    'truthy',
                    'related_comment_friend_only',
                ],
                'when' => [
                    'includes',
                    'when',
                    WhenScope::getAllowWhen(),
                ],
                'sort' => [
                    'includes',
                    'sort',
                    [
                        Browse::SORT_RECENT,
                        Browse::SORT_MOST_LIKED,
                        Browse::SORT_MOST_VIEWED,
                        Browse::SORT_MOST_DISCUSSED,
                    ],
                ],
            ]);

        $this->add('viewAllSectionItem')
            ->apiUrl('search')
            ->apiParams([
                'q'                           => ':q',
                'limit'                       => 2,
                'owner_id'                    => ':owner_id',
                'page'                        => ':page',
                'last_search_id'              => ':last_search_id',
                'when'                        => ':when',
                'sort'                        => ':sort',
                'view'                        => ':item_type',
                'is_hashtag'                  => ':is_hashtag',
                'hashtag'                     => ':hashtag',
                'from'                        => ':from',
                'related_comment_friend_only' => ':related_comment_friend_only',
            ])
            ->apiRules([
                'q'                           => ['truthy', 'q'],
                'limit'                       => ['truthy', 'limit'],
                'owner_id'                    => ['truthy', 'owner_id'],
                'page'                        => ['truthy', 'limit'],
                'last_search_id'              => ['truthy', 'limit'],
                'view'                        => ['truthy', 'view'],
                'is_hashtag'                  => ['truthy', 'is_hashtag'],
                'from'                        => ['truthy', 'from'],
                'hashtag'                     => ['truthy', 'hashtag'],
                'related_comment_friend_only' => ['truthy', 'related_comment_friend_only'],
                'when'                        => [
                    'includes',
                    'when',
                    WhenScope::getAllowWhen(),
                ],
                'sort' => [
                    'includes',
                    'sort',
                    [
                        Browse::SORT_RECENT,
                        Browse::SORT_MOST_LIKED,
                        Browse::SORT_MOST_VIEWED,
                        Browse::SORT_MOST_DISCUSSED,
                    ],
                ],
            ]);

        $this->add('viewAllSearch')
            ->apiUrl('search')
            ->apiParams([
                'q'                           => ':q',
                'limit'                       => Pagination::DEFAULT_ITEM_PER_PAGE,
                'owner_id'                    => ':owner_id',
                'page'                        => ':page',
                'last_search_id'              => ':last_search_id',
                'when'                        => ':when',
                'sort'                        => ':sort',
                'view'                        => ':item_type',
                'is_hashtag'                  => ':is_hashtag',
                'hashtag'                     => ':hashtag',
                'from'                        => ':from',
                'related_comment_friend_only' => ':related_comment_friend_only',
            ])
            ->apiRules([
                'q'                           => ['truthy', 'q'],
                'limit'                       => ['truthy', 'limit'],
                'page'                        => ['truthy', 'limit'],
                'last_search_id'              => ['truthy', 'limit'],
                'owner_id'                    => ['truthy', 'owner_id'],
                'view'                        => ['truthy', 'view'],
                'is_hashtag'                  => ['truthy', 'is_hashtag'],
                'from'                        => ['truthy', 'from'],
                'hashtag'                     => ['truthy', 'hashtag'],
                'related_comment_friend_only' => ['truthy', 'related_comment_friend_only'],
                'when'                        => [
                    'includes',
                    'when',
                    WhenScope::getAllowWhen(),
                ],
                'sort' => [
                    'includes',
                    'sort',
                    [
                        Browse::SORT_RECENT,
                        Browse::SORT_MOST_LIKED,
                        Browse::SORT_MOST_VIEWED,
                        Browse::SORT_MOST_DISCUSSED,
                    ],
                ],
            ]);
    }
}
