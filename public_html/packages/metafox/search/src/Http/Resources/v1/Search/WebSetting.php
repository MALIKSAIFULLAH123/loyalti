<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Search\Http\Resources\v1\Search;

use MetaFox\Platform\Resource\WebSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;
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
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->pageUrl('search');

        $this->add('searchHashtagItem')
            ->pageUrl('hashtash/search')
            ->pageParams([
                'is_hashtag'                  => 1,
                'from'                        => Browse::VIEW_ALL,
                'view'                        => Browse::VIEW_ALL,
                'related_comment_friend_only' => 0,
            ]);

        $this->add('viewSections')
            ->apiUrl('search/group')
            ->apiParams([
                'q'                           => ':q',
                'is_hashtag'                  => ':is_hashtag',
                'from'                        => ':from',
                'related_comment_friend_only' => ':related_comment_friend_only',
            ])
            ->asGet()
            ->apiRules([
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
                'related_comment_friend_only' => [
                    'truthy',
                    'related_comment_friend_only',
                ],
            ]);

        $this->add('viewAllSectionItem')
            ->apiUrl('search')
            ->apiParams([
                'q'                           => ':q',
                'limit'                       => 2,
                'last_search_id'              => ':last_search_id',
                'view'                        => ':item_type',
                'is_hashtag'                  => ':is_hashtag',
                'from'                        => ':from',
                'related_comment_friend_only' => ':related_comment_friend_only',
            ])
            ->apiRules([
                'q'                           => ['truthy', 'q'],
                'limit'                       => ['truthy', 'limit'],
                'last_search_id'              => ['truthy', 'last_search_id'],
                'view'                        => ['truthy', 'view'],
                'is_hashtag'                  => ['truthy', 'is_hashtag'],
                'from'                        => ['truthy', 'from'],
                'related_comment_friend_only' => ['truthy', 'related_comment_friend_only'],
            ]);

        $this->add('viewAll')
            ->apiUrl('search')
            ->apiParams([
                'q'                           => ':q',
                'limit'                       => Pagination::DEFAULT_ITEM_PER_PAGE,
                'last_search_id'              => ':last_search_id',
                'view'                        => ':item_type',
                'is_hashtag'                  => ':is_hashtag',
                'from'                        => ':from',
                'related_comment_friend_only' => ':related_comment_friend_only',
            ])
            ->apiRules([
                'q'                           => ['truthy', 'q'],
                'limit'                       => ['truthy', 'limit'],
                'last_search_id'              => ['truthy', 'last_search_id'],
                'view'                        => ['truthy', 'view'],
                'is_hashtag'                  => ['truthy', 'is_hashtag'],
                'from'                        => ['truthy', 'from'],
                'related_comment_friend_only' => ['truthy', 'related_comment_friend_only'],
            ]);

        $this->add('viewSuggestions')
            ->apiUrl('search/suggestion')
            ->asGet()
            ->apiRules([
                'q'     => ['truthy', 'q'],
                'limit' => ['truthy', 'limit'],
            ]);

        $this->add('hashtagTrending')
            ->apiUrl('search/hashtag/trending')
            ->apiRules([])
            ->asGet();
    }
}
