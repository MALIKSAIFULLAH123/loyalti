<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use MetaFox\Friend\Support\Browse\Scopes\Friend\SortScope;
use MetaFox\Friend\Support\Browse\Scopes\Friend\ViewBirthdayFriendsScope;
use MetaFox\Friend\Support\Browse\Scopes\Friend\WhenScope;
use MetaFox\Platform\Resource\WebSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Friend Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->pageUrl('friend/search')
            ->placeholder(__p('friend::phrase.search_friends'));

        $this->add('viewAll')
            ->apiUrl('friend')
            ->pageUrl('friend')
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'list_id'     => ['truthy', 'list_id'],
                'is_featured' => ['truthy', 'is_featured'],
                'sort'        => [
                    'includes', 'sort', SortScope::getAllowSort(),
                ],
                'when'        => ['includes', 'when', WhenScope::getAllowWhen()],
            ]);

        $this->add('suggestItem')
            ->apiUrl('friend/suggestion');

        $this->add('getTagSuggestion')
            ->apiUrl('friend/tag-suggestion')
            ->asGet()
            ->apiParams([
                'q'            => ':q',
                'item_id'      => ':item_id',
                'item_type'    => ':item_type',
                'excluded_ids' => ':excluded_ids',
                'user_id'      => ':user_id',
                'owner_id'     => ':owner_id',
            ]);

        $this->add('getForMention')
            ->apiUrl('friend/mention')
            ->asGet()
            ->apiParams([
                'q' => ':q',
            ]);

        $this->add('getForMentionFriends')
            ->apiUrl('friend/mention')
            ->asGet()
            ->apiParams([
                'q'    => ':q',
                'view' => 'friend',
            ]);

        $this->add('viewProfile')
            ->apiUrl('friend')
            ->asGet()
            ->apiParams([
                'user_id' => ':user_id',
                'view'    => 'profile',
                'limit'   => 6,
            ]);

        $this->add('shareOnFriendProfile')
            ->apiUrl('friend/share-suggestion')
            ->apiParams([
                'view'             => 'friend',
                'limit'            => 10,
                'share_on_profile' => 1,
            ]);

        $this->add('getFriendBirthday')
            ->apiUrl('friend/birthday')
            ->asGet()
            ->apiParams([
                'view'  => Browse::VIEW_ALL,
                'limit' => ':limit',
            ]);

        $this->add('upcomingFriendBirthday')
            ->apiUrl('friend/birthday')
            ->asGet()
            ->apiParams([
                'view'  => ViewBirthdayFriendsScope::VIEW_UPCOMING,
                'limit' => ':limit',
            ]);

        $this->add('hideUserSuggestion')
            ->asPost()
            ->apiUrl('friend/suggestion/hide')
            ->apiParams([
                'user_id' => ':user_id',
            ]);

        $this->add('viewMutualFriend')
            ->apiUrl('friend')
            ->pageUrl('friend')
            ->apiParams([
                'view'    => 'mutual',
                'user_id' => ':id',
            ]);
    }
}
