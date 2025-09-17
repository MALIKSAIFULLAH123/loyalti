<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use MetaFox\Friend\Support\Browse\Scopes\Friend\SortScope;
use MetaFox\Friend\Support\Browse\Scopes\Friend\WhenScope;
use MetaFox\Platform\Resource\MobileSetting as Setting;
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
 * @ignore
 * @codeCoverageIgnore
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->apiUrl('friend')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'is_featured' => ':is_featured',
            ])
            ->placeholder(__p('friend::phrase.search_friends'));

        $this->add('viewAll')
            ->apiUrl('friend')
            ->pageUrl('friend')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'is_featured' => ':is_featured',
                'list_id'     => ':list_id',
            ])
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'is_featured' => ['truthy', 'is_featured'],
                'list_id'     => ['truthy', 'list_id'],
                'sort'        => [
                    'includes', 'sort', SortScope::getAllowSort(),
                ],
                'when'        => ['includes', 'when', WhenScope::getAllowWhen()],
            ]);

        $this->add('suggestItem')
            ->apiUrl('friend/suggestion');

        $this->add('viewOnOwner')
            ->apiUrl('friend')
            ->pageUrl('friend')
            ->apiParams([
                'user_id' => ':id',
            ]);

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

        $this->add('viewFriendsByList')
            ->apiUrl('friend')
            ->apiParams(['list_id' => ':list_id']);

        $this->add('shareOnFriendProfile')
            ->apiUrl('friend/share-suggestion')
            ->apiParams([
                'q'                => ':q',
                'view'             => 'friend',
                'limit'            => 10,
                'share_on_profile' => 1,
            ]);

        $this->add('searchInOwner')
            ->apiUrl('friend')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('friend::phrase.search_friends'));

        $this->add('viewMutualFriend')
            ->apiUrl('friend')
            ->pageUrl('friend')
            ->apiParams([
                'view'    => 'mutual',
                'user_id' => ':user_id',
            ]);

        $this->add('getFriendBirthday')
            ->apiUrl('friend/birthday')
            ->asGet()
            ->apiParams([
                'view'  => Browse::VIEW_ALL,
                'limit' => 20,
            ]);

        $this->add('hideUserSuggestion')
            ->asPost()
            ->apiUrl('friend/suggestion/hide')
            ->apiParams([
                'user_id' => ':user_id',
            ]);
    }
}
