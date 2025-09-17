<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1\Event;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Support\Browse\Scopes\Event\SortScope;
use MetaFox\Event\Support\Browse\Scopes\Event\ViewScope;
use MetaFox\Event\Support\Browse\Scopes\Event\WhenScope;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Resource\WebSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as ScopesSortScope;

/**
 *--------------------------------------------------------------------------
 * Event Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('event');

        $this->add('searchItem')
            ->pageUrl('event/search')
            ->pageParams([
                'when' => Browse::WHEN_ALL,
                'view' => Browse::VIEW_SEARCH,
            ])
            ->placeholder(__p('event::phrase.search_events'));

        $this->add('viewAll')
            ->apiUrl('event')
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'where'       => [
                    'truthy',
                    'where',
                ],
                'sort'        => [
                    'includes',
                    'sort',
                    SortScope::getAllowSort(),
                ],
                'category_id' => [
                    'truthy',
                    'category_id',
                ],
                'when'        => [
                    'includes',
                    'when',
                    WhenScope::getAllowWhen(),
                ],
                'view'        => [
                    'includes',
                    'view',
                    ViewScope::getAllowView(),
                ],
                'is_online'   => ['truthy', 'is_online'],
                'is_featured' => ['truthy', 'is_featured'],
            ]);

        $this->add('viewItemsOnMap')
            ->apiUrl('event')
            ->apiRules([
                'q'            => ['truthy', 'q'],
                'where'        => [
                    'truthy',
                    'where',
                ],
                'sort'         => [
                    'includes',
                    'sort',
                    SortScope::getAllowSort(),
                ],
                'when'         => [
                    'includes',
                    'when',
                    WhenScope::getAllowWhen(),
                ],
                'view'         => [
                    'includes',
                    'view',
                    ViewScope::getAllowView(),
                ],
                'limit'        => [
                    'includes',
                    'limit',
                    [
                        MetaFoxConstant::VIEW_5_NEAREST,
                        MetaFoxConstant::VIEW_10_NEAREST,
                        MetaFoxConstant::VIEW_15_NEAREST,
                        MetaFoxConstant::VIEW_20_NEAREST,
                    ],
                ],
                'bounds_west'  => ['truthy', 'bounds_west'],
                'bounds_east'  => ['truthy', 'bounds_east'],
                'bounds_south' => ['truthy', 'bounds_south'],
                'bounds_north' => ['truthy', 'bounds_north'],
                'zoom'         => ['truthy', 'zoom'],
                'is_featured'  => ['truthy', 'is_featured'],
            ]);

        $this->add('viewSimilar')
            ->apiUrl('event')
            ->apiParams([
                'event_id' => ':id',
                'view'     => Browse::VIEW_SIMILAR,
                'sort'     => SortScope::SORT_RANDOM,
                'limit'    => 1,
            ]);

        $this->add('viewInterested')
            ->apiUrl('event')
            ->apiParams([
                'sort' => SortScope::SORT_DEFAULT,
                'view' => ViewScope::VIEW_INTERESTED,
            ]);

        $this->add('viewHosting')
            ->apiUrl('event')
            ->apiParams([
                'sort' => SortScope::SORT_DEFAULT,
                'view' => ViewScope::VIEW_HOSTING,
            ]);

        $this->add('viewMyPendingEvent')
            ->apiUrl('event')
            ->apiParams([
                'view' => Browse::VIEW_MY_PENDING,
            ]);

        $this->add('viewGoing')
            ->apiUrl('event')
            ->apiParams([
                'sort' => SortScope::SORT_DEFAULT,
                'view' => ViewScope::VIEW_GOING,
            ]);

        $this->add('viewPast')
            ->apiUrl('event')
            ->apiParams([
                'sort' => SortScope::SORT_DEFAULT,
                'when' => WhenScope::WHEN_PAST,
            ]);

        $this->add('viewRelatedPast')
            ->apiUrl('event')
            ->apiParams([
                'sort' => SortScope::SORT_END_TIME,
                'view' => ViewScope::VIEW_RELATED,
                'when' => WhenScope::WHEN_PAST,
            ]);

        $this->add('viewUpcoming')
            ->apiUrl('event')
            ->apiParams([
                'sort'      => SortScope::SORT_UPCOMING,
                'sort_type' => Browse::SORT_TYPE_ASC,
                'when'      => WhenScope::WHEN_UPCOMING,
                'user_id'   => ':id',
            ]);

        $this->add('viewItem')
            ->apiUrl('event/:id')
            ->pageUrl('event/:id')
            ->apiParams([
                'invite_code' => ':invite_code',
            ]);

        $this->add('approveItem')
            ->apiUrl('event/approve/:id')
            ->asPatch();

        $this->add('deleteItem')
            ->apiUrl('event/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('event::phrase.confirm_delete'),
                ]
            );

        $this->add('addItem')
            ->apiUrl('core/form/event.store')
            ->pageUrl('event/add');

        $this->add('editItem')
            ->apiUrl('core/form/event.update/:id')
            ->pageUrl('event/edit/:id');

        $this->add('editFeedItem')
            ->apiUrl('event/form/event.update/:id')
            ->pageUrl('event/edit/:id');

        $this->add('sponsorItem')
            ->apiUrl('event/sponsor/:id')
            ->asPatch();

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('event/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('event/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('exportItem')
            ->apiUrl('event/:id/export');

        $this->add('invitePeopleToCome')
            ->apiUrl('event-invite')
            ->asPost()
            ->apiParams([
                'event_id' => ':id',
                'user_ids' => ':ids',
            ]);

        $this->add('massInvite')
            ->apiUrl('event/:id/mass-invite')
            ->asPatch();

        $this->add('inviteHosts')
            ->apiUrl('event-host-invite')
            ->asPost()
            ->apiParams([
                'event_id' => ':id',
                'user_ids' => ':ids',
            ]);

        $this->add('suggestFriends')
            ->apiUrl('friend/invite-to-item')
            ->asGet()
            ->apiParams([
                'q'         => ':q',
                'owner_id'  => ':owner_id',
                'item_id'   => ':id',
                'item_type' => Event::ENTITY_TYPE,
            ]);

        $this->add('suggestHosts')
            ->apiUrl('friend/invite-to-item')
            ->asGet()
            ->apiParams([
                'q'         => ':q',
                'owner_id'  => ':owner_id',
                'item_id'   => ':id',
                'item_type' => Event::ENTITY_TYPE,
            ]);

        $this->add('joinEvent')
            ->apiUrl('event-member')
            ->asPost()
            ->apiParams([
                'event_id'    => ':id',
                'invite_code' => ':invite_code',
            ]);

        $this->add('leaveEvent')
            ->apiUrl('event-member/:id')
            ->asDelete()
            ->confirm([
                'title'   => 'Confirm',
                'message' => 'Are you sure you want to leave this event?',
            ]);

        $this->add('interestedEvent')
            ->apiUrl('event-member/interest/:id')
            ->asPut()
            ->apiParams([
                'interest'    => Member::INTERESTED,
                'invite_code' => ':invite_code',
            ]);

        $this->add('notInterestedEvent')
            ->apiUrl('event-member/interest/:id')
            ->asPut()
            ->apiParams(['interest' => Member::NOT_INTERESTED]);

        $this->add('settingForm')
            ->apiUrl('event/setting/form/:id')
            ->asGet()
            ->apiParams(['event_id' => ':id']);

        $this->add('viewPendingPost')
            ->apiUrl('feed')
            ->asGet()
            ->apiParams([
                'user_id'      => ':id',
                'status'       => MetaFoxConstant::ITEM_STATUS_PENDING,
                'has_pin_post' => 0,
            ])
            ->apiRules(['sort_type' => ['includes', 'sort_type', ScopesSortScope::getAllowSortType()]]);

        $this->add('viewCreatorPendingPost')
            ->apiUrl('feed')
            ->asGet()
            ->apiParams([
                'user_id'      => ':id', 'status' => MetaFoxConstant::ITEM_STATUS_PENDING,
                'view'         => Browse::VIEW_YOUR_CONTENT,
                'has_pin_post' => 0,
            ])
            ->apiRules(['sort_type' => ['includes', 'sort_type', ScopesSortScope::getAllowSortType()]]);

        $this->add('viewStats')
            ->asGet()
            ->apiUrl('event/:id/stats');

        $this->add('viewUserStats')
            ->asGet()
            ->apiUrl('user/:user_id/item-stats')
            ->apiParams([
                'item_type' => Event::ENTITY_TYPE,
                'item_id'   => ':id',
            ]);

        $this->add('viewAllEventsUpcoming')
            ->asGet()
            ->apiUrl('event')
            ->apiParams([
                'sort'      => SortScope::SORT_UPCOMING,
                'sort_type' => Browse::SORT_TYPE_ASC,
                'view'      => Browse::WHEN_ALL,
                'when'      => WhenScope::WHEN_UPCOMING,
            ]);

        $this->add('viewAllEventsOnGoing')
            ->apiUrl('event')
            ->apiParams([
                'sort' => SortScope::SORT_DEFAULT,
                'view' => Browse::WHEN_ALL,
                'when' => WhenScope::WHEN_ONGOING,
            ]);

        $this->add('viewFriendEventsUpcoming')
            ->asGet()
            ->apiUrl('event')
            ->apiParams([
                'sort'      => SortScope::SORT_UPCOMING,
                'sort_type' => Browse::SORT_TYPE_ASC,
                'view'      => Browse::VIEW_FRIEND,
                'when'      => WhenScope::WHEN_UPCOMING,
            ]);

        $this->add('viewFriendEventsOnGoing')
            ->apiUrl('event')
            ->apiParams([
                'sort' => SortScope::SORT_DEFAULT,
                'view' => Browse::VIEW_FRIEND,
                'when' => WhenScope::WHEN_ONGOING,
            ]);

        $this->add('viewHostingUpcoming')
            ->asGet()
            ->apiUrl('event')
            ->apiParams([
                'sort'      => SortScope::SORT_UPCOMING,
                'sort_type' => Browse::SORT_TYPE_ASC,
                'view'      => ViewScope::VIEW_HOSTING,
                'when'      => WhenScope::WHEN_UPCOMING,
            ]);

        $this->add('viewHostingOnGoing')
            ->apiUrl('event')
            ->apiParams([
                'sort' => SortScope::SORT_DEFAULT,
                'view' => ViewScope::VIEW_HOSTING,
                'when' => WhenScope::WHEN_ONGOING,
            ]);

        $this->add('viewHostingPast')
            ->apiUrl('event')
            ->apiParams([
                'sort' => SortScope::SORT_DEFAULT,
                'view' => ViewScope::VIEW_HOSTING,
                'when' => WhenScope::WHEN_PAST,
            ]);

        $this->add('massEmailEvent')
            ->apiUrl('core/form/event.mass_email/:id')
            ->asGet();

        $this->add('sponsorItemInFeed')
            ->apiUrl('event/sponsor-in-feed/:id')
            ->asPatch();

        $this->add('updateProfileCover')
            ->apiUrl('event/banner/:id')
            ->asPost();

        $this->add('removeProfileCover')
            ->apiUrl('event/banner/:id')
            ->asDelete()
            ->confirm(['message' => __p('photo::phrase.delete_confirm')]);

        $this->add('duplicateEvent')
            ->apiUrl('core/form/event.duplicate/:id')
            ->pageUrl('event/duplicate');
    }
}
