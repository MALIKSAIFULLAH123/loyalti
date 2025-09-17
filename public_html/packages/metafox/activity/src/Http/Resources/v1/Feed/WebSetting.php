<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Http\Resources\v1\Feed;

use MetaFox\Activity\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Resource\WebSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Feed Web Resource Setting
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
        $this->add('viewAll')
            ->apiUrl('/feed')
            ->apiRules([
                'q'                           => ['truthy', 'q'],
                'related_comment_friend_only' => [
                    'or', ['truthy', 'related_comment_friend_only'], ['falsy', 'related_comment_friend_only'],
                ],
                'sort'    => ['includes', 'sort', SortScope::getAllowSort()],
                'from'    => ['includes', 'from', ['all', 'user', 'page', 'group']],
                'view'    => ['truthy', 'view'],
                'type_id' => ['truthy', 'type_id'],
                'user_id' => ['truthy', 'user_id'],
            ])->apiParams([
                'q'                           => ':q',
                'related_comment_friend_only' => ':related_comment_friend_only',
                'sort'                        => ':sort',
                'from'                        => ':from',
                'view'                        => ':view',
                'type_id'                     => ':type_id',
                'user_id'                     => ':id',
            ]);

        $this->add('viewItem')
            ->apiUrl('feed/:id')
            ->pageUrl('feed/:id')
            ->apiParams([
                'comment_id' => ':comment_id',
            ]);

        $this->add('viewOwnerItem')
            ->apiUrl('feed/:feed_id')
            ->apiParams([
                'comment_id' => ':comment_id',
            ]);

        $this->add('editItem')
            ->apiUrl('feed/edit/:id')
            ->asGet();

        $this->add('deleteItem')
            ->apiUrl('feed/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('activity::phrase.delete_confirm'),
                ]
            );

        $this->add('deleteWithItems')
            ->asDelete()
            ->apiUrl('feed/items/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('activity::phrase.delete_with_items_confirm'),
                ]
            );

        $this->add('hideItem')
            ->apiUrl('feed/hide-feed/:id')
            ->asPost();

        $this->add('undoHideItem')
            ->apiUrl('feed/hide-feed/:id')
            ->asDelete();

        $this->add('approvePending')
            ->apiUrl('feed/approve/:id')
            ->asPatch();

        $this->add('declinePending')
            ->apiUrl('feed/decline/:id')
            ->asPatch();

        $this->add('updatePrivacy')
            ->apiUrl('feed/privacy/:id')
            ->asPatch();

        $this->add('declinePendingAndBlockAuthor')
            ->apiUrl('core/form/activity.block_author_form/:id')
            ->asGet();

        $this->add('pinHome')
            ->apiUrl('feed/pin/:id/home')
            ->confirm(['message' => __p('activity::web.pin_hom_confirm_desc')])
            ->asPost();

        $this->add('unpinHome')
            ->apiUrl('feed/pin/:id/home')
            ->asDelete();

        $this->add('pinItem')
            ->apiUrl('feed/pin/:id')
            ->asPost();

        $this->add('unpinItem')
            ->apiUrl('feed/unpin/:id')
            ->asDelete();

        $this->add('removeTaggedFriend')
            ->apiUrl('feed/tag/:id')
            ->asDelete();

        $this->add('reviewTagStreams')
            ->apiUrl('feed')
            ->apiRules([
                'q' => ['truthy', 'q'], 'related_comment_friend_only' => [
                    'or', ['truthy', 'related_comment_friend_only'], ['falsy', 'related_comment_friend_only'],
                ], 'sort' => [
                    'includes', 'sort', ['recent', 'most_viewed', 'most_liked', 'most_discussed'],
                ], 'from' => ['includes', 'from', ['all', 'user', 'page', 'group']],
                'view' => ['truthy', 'view'], 'type_id' => ['truthy', 'type_id'],
            ])
            ->apiParams([
                'is_preview_tag' => 1,
                'has_pin_post'   => 0,
            ]);

        $this->add('allowed')
            ->apiUrl('feed/allow-preview/:id')
            ->asPatch()
            ->apiParams(['is_allowed' => 1]);

        $this->add('hideOnTimeline')
            ->apiUrl('feed/allow-preview/:id')
            ->asPatch()
            ->apiParams(['is_allowed' => 0]);

        $this->add('removeItem')
            ->apiUrl('feed/archive/:id')
            ->asPatch();

        $this->add('checkNew')
            ->apiUrl('feed/check-new')
            ->asGet()
            ->apiParams([
                'last_feed_id'           => ':last_feed_id',
                'last_pin_feed_id'       => ':last_pin_feed_id',
                'last_sponsored_feed_id' => ':last_sponsored_feed_id',
                'sort'                   => ':sort',
            ]);

        $this->add('shareNow')
            ->apiUrl('feed/share')
            ->apiParams([
                'item_id'      => ':item_id',
                'item_type'    => ':item_type',
                'post_content' => ':post_content',
                'post_type'    => ':post_type',
                'privacy'      => ':privacy',
            ])
            ->asPost();

        $this->add('shareToNewsFeed');

        $this->add('copyLink');

        $this->add('taggedFriends')
            ->asGet()
            ->apiUrl('feed/tagged-friend')
            ->apiParams([
                'item_id'      => ':item_id',
                'item_type'    => ':item_type',
                'excluded_ids' => ':excluded_ids',
            ]);

        $this->add('viewCreatorDeclinedPost')
            ->apiUrl('feed')
            ->asGet()
            ->apiParams([
                'user_id'      => ':id',
                'status'       => MetaFoxConstant::ITEM_STATUS_DENIED,
                'view'         => Browse::VIEW_YOUR_CONTENT,
                'has_pin_post' => 0,
            ])
            ->apiRules(['sort_type' => ['includes', 'sort_type', SortScope::getAllowSortType()]]);

        $this->add('viewCreatorPendingPost')
            ->apiUrl('feed')
            ->asGet()
            ->apiParams([
                'user_id'      => ':id',
                'status'       => MetaFoxConstant::ITEM_STATUS_PENDING,
                'view'         => Browse::VIEW_YOUR_CONTENT,
                'has_pin_post' => 0,
            ])
            ->apiRules(['sort_type' => ['includes', 'sort_type', SortScope::getAllowSortType()]]);

        $this->add('viewCreatorPublishedPost')
            ->apiUrl('feed')
            ->asGet()
            ->apiParams([
                'user_id'      => ':id',
                'status'       => MetaFoxConstant::ITEM_STATUS_APPROVED,
                'view'         => Browse::VIEW_YOUR_CONTENT,
                'has_pin_post' => 0,
            ])
            ->apiRules(['sort_type' => ['includes', 'sort_type', SortScope::getAllowSortType()]]);

        $this->add('viewCreatorRemovedPost')
            ->apiUrl('feed')
            ->asGet()
            ->apiParams([
                'user_id'      => ':id',
                'status'       => MetaFoxConstant::ITEM_STATUS_REMOVED,
                'view'         => Browse::VIEW_YOUR_CONTENT,
                'has_pin_post' => 0,
            ]);

        $this->add('translateItem')
            ->apiUrl('feed/translate')
            ->apiParams([
                'id'     => ':id',
                'target' => ':target',
            ])
            ->asGet();

        $this->add('updateSort')
            ->apiUrl('feed/setting/sort')
            ->apiParams([
                'user_id' => ':id',
                'sort'    => ':sort',
            ])
            ->asPatch();
    }
}
