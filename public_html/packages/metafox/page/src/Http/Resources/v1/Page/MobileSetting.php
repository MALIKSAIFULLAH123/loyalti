<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Support\Browse\Scopes\Page\ViewScope;
use MetaFox\Platform\Resource\MobileSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->apiUrl('page')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'category_id' => ':category_id',
                'is_featured' => ':is_featured',
                'view'        => 'search',
            ])
            ->placeholder(__p('page::phrase.search_pages'));

        $this->add('reassignOwnerForm')
            ->apiUrl('core/mobile/form/page.page_member.reassign_owner/:id')
            ->asGet();

        $this->add('updateProfileCover')
            ->apiUrl('page/cover/:id')
            ->asPost();

        $this->add('removeProfileCover')
            ->apiUrl('page/cover/:id')
            ->asDelete()
            ->confirm(['message' => __p('page::phrase.are_you_sure_you_want_to_delete_this_photo')]);

        $this->add('updateAvatar')
            ->apiUrl('page/avatar/:id');

        $this->add('viewAll')
            ->apiUrl('page')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'category_id' => ':category_id',
                'is_featured' => ':is_featured',
                'view'        => ':view',
            ])
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'sort'        => ['includes', 'sort', ['recent', 'most_viewed', 'most_member', 'most_discussed']],
                'type_id'     => ['truthy', 'type_id'],
                'category_id' => ['truthy', 'category_id'],
                'is_featured' => ['truthy', 'is_featured'],
                'when'        => ['includes', 'when', ['all', 'this_month', 'this_week', 'today']],
                'view'        => ['includes', 'view', ['my', 'friend', 'pending', 'invited', 'liked']],
            ]);

        $this->add('viewSearchInPage')
            ->apiUrl('search')
            ->apiParams([
                'owner_id'                    => ':id',
                'q'                           => ':q',
                'view'                        => ':item_type',
                'when'                        => ':when',
                'related_comment_friend_only' => ':related_comment_friend_only',
                'sort'                        => ':sort',
                'limit'                       => Pagination::DEFAULT_ITEM_PER_PAGE,
                'last_search_id'              => ':last_search_id',
            ])
            ->apiRules([
                'q'                           => ['truthy', 'q'],
                'view'                        => ['truthy', 'view'],
                'when'                        => [
                    'includes',
                    'when',
                    [
                        'this_month',
                        'this_week',
                        'today',
                        'all',
                    ],
                ],
                'sort'                        => [
                    'includes',
                    'sort',
                    [
                        Browse::SORT_RECENT,
                        Browse::SORT_MOST_LIKED,
                        Browse::SORT_MOST_VIEWED,
                        Browse::SORT_MOST_DISCUSSED,
                    ],
                ],
                'related_comment_friend_only' => ['truthy', 'related_comment_friend_only'],
                'owner_id'                    => ['truthy', 'owner_id'],
                'last_search_id'              => ['truthy', 'last_search_id'],
            ]);

        $this->add('viewSearchSectionItemInPage')
            ->apiUrl('search')
            ->apiParams([
                'owner_id'                    => ':id',
                'q'                           => ':q',
                'view'                        => ':item_type',
                'when'                        => ':when',
                'related_comment_friend_only' => ':related_comment_friend_only',
                'sort'                        => ':sort',
                'limit'                       => 2,
                'last_search_id'              => ':last_search_id',
            ])
            ->apiRules([
                'q'                           => ['truthy', 'q'],
                'view'                        => ['truthy', 'view'],
                'when'                        => [
                    'includes',
                    'when',
                    [
                        'this_month',
                        'this_week',
                        'today',
                        'all',
                    ],
                ],
                'sort'                        => [
                    'includes',
                    'sort',
                    [
                        Browse::SORT_RECENT,
                        Browse::SORT_MOST_LIKED,
                        Browse::SORT_MOST_VIEWED,
                        Browse::SORT_MOST_DISCUSSED,
                    ],
                ],
                'related_comment_friend_only' => ['truthy', 'related_comment_friend_only'],
                'owner_id'                    => ['truthy', 'owner_id'],
                'last_search_id'              => ['truthy', 'last_search_id'],
            ]);

        $this->add('viewSearchSectionsInPage')
            ->apiUrl('search/group')
            ->apiParams([
                'owner_id'                    => ':id',
                'q'                           => ':q',
                'when'                        => ':when',
                'sort'                        => ':sort',
                'related_comment_friend_only' => ':related_comment_friend_only',
            ])
            ->apiRules([
                'q'                           => ['truthy', 'q'],
                'owner_id'                    => ['truthy', 'owner_id'],
                'when'                        => [
                    'includes',
                    'when',
                    [
                        'this_month',
                        'this_week',
                        'today',
                        'all',
                    ],
                ],
                'sort'                        => [
                    'includes',
                    'sort',
                    [
                        Browse::SORT_RECENT,
                        Browse::SORT_MOST_LIKED,
                        Browse::SORT_MOST_VIEWED,
                        Browse::SORT_MOST_DISCUSSED,
                    ],
                ],
                'related_comment_friend_only' => ['truthy', 'related_comment_friend_only'],
            ]);

        $this->add('viewItem')
            ->apiUrl('page/:id')
            ->pageUrl('pages/:id');

        $this->add('deleteItem')
            ->apiUrl('page/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('page::phrase.delete_confirm'),
                ]
            );

        $this->add('addItem')
            ->pageUrl('pages/add')
            ->apiUrl('core/mobile/form/page.page.store');

        $this->add('editItem')
            ->pageUrl('pages/settings/:id')
            ->apiUrl('');

        $this->add('sponsorItem')
            ->asPatch()
            ->apiUrl('page/sponsor/:id');

        /*
        * @deprecated Remove in 5.2.0
        */
        $this->add('featureItem')
            ->apiUrl('page/feature/:id');

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('page/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('page/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('suggestItem')
            ->apiUrl('page/suggestion');

        $this->add('getPageInfoForm')
            ->apiUrl('core/mobile/form/page.page.info/:id')
            ->asGet();

        $this->add('getPageAboutForm')
            ->apiUrl('core/mobile/form/page.page.about/:id')
            ->asGet();

        $this->add('pageInfo')
            ->apiUrl('page-info/:id')
            ->asGet();

        $this->add('getPagePermission')
            ->apiUrl('page/privacy/:id')
            ->asGet();

        $this->add('getPagePermissionForm')
            ->apiUrl('core/mobile/form/page.page.permission/:id')
            ->asGet();

        $this->add('updatePagePermission')
            ->apiUrl('page/privacy/:id')
            ->asPut();

        $this->add('inviteFriends')
            ->apiUrl('friend/invite-to-owner')
            ->asGet()
            ->apiParams(['q' => ':q', 'owner_id' => ':id', 'privacy_type' => Page::PAGE_MEMBERS]);

        $this->add('viewSimilar')
            ->apiUrl('page/similar')
            ->apiParams([
                'page_id' => ':id',
                'limit'   => 5,
                'when'    => ':when',
                'sort'    => ':sort',
            ]);

        $this->add('viewMyPages')
            ->apiUrl('page')
            ->apiParams(['view' => 'my']);

        $this->add('viewOnOwner')
            ->apiUrl('page')
            ->apiParams(['user_id' => ':id']);

        $this->add('viewFriendPages')
            ->apiUrl('page')
            ->apiParams(['view' => 'friend']);

        $this->add('viewLikedPages')
            ->apiUrl('page')
            ->apiParams(['view' => 'liked']);

        $this->add('viewInvitedPages')
            ->apiUrl('page')
            ->apiParams(['view' => 'invited']);

        $this->add('viewPendingPages')
            ->apiUrl('page')
            ->apiParams(['view' => 'pending']);

        $this->add('searchGlobalPage')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'       => 'page',
                'q'          => ':q',
                'is_hashtag' => ':is_hashtag',
            ]);

        $this->add('viewMyPendingPages')
            ->apiUrl('page')
            ->apiParams([
                'view' => 'my_pending',
            ]);
        $this->add('approveItem')
            ->apiUrl('page/approve/:id')
            ->asPatch();

        $this->add('searchGlobalInPage')
            ->apiUrl(apiUrl('search.group.index'))
            ->apiParams([
                'q'                           => ':q',
                'owner_id'                    => ':owner_id',
                'when'                        => ':when',
                'related_comment_friend_only' => ':related_comment_friend_only',
            ]);

        $this->add('follow')
            ->apiUrl('follow')
            ->asPost()
            ->apiParams([
                'user_id' => ':id',
            ]);

        $this->add('unfollow')
            ->apiUrl('follow/:id')
            ->asDelete();

        $this->add('shareOnPageProfile')
            ->apiUrl('page/share-suggestion')
            ->apiParams(
                [
                    'q'     => ':q',
                    'view'  => ViewScope::VIEW_LIKED,
                    'limit' => 10,
                ]
            );

        $this->add('searchInOwner')
            ->apiUrl('page')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('page::phrase.search_pages'));

        $this->add('searchMember')
            ->apiUrl('search-page-member')
            ->apiParams([
                'q'       => ':q',
                'page_id' => ':id',
                'view'    => ':view',
            ]);

        $this->add('getForMentionInFeed')
            ->asGet()
            ->apiUrl('friend/mention')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':owner_id',
                'user_id'  => ':user_id',
            ]);

        $this->add('getForTagMembersInFeed')
            ->asGet()
            ->apiUrl('friend/mention')
            ->apiParams([
                'q'              => ':q',
                'user_id'        => ':user_id',
                'owner_id'       => ':owner_id',
                'is_member_only' => 1,
            ]);

        $this->add('getPageInfoSettings')
            ->apiUrl('page/info-setting/:id')
            ->asGet();

        $this->add('getPageAboutSettings')
            ->apiUrl('page/about-setting/:id')
            ->asGet();

        $this->add('getPageSchedulePost')
            ->apiUrl('feed-schedule')
            ->asGet()
            ->apiParams([
                'entity_id'   => ':id',
                'entity_type' => Page::ENTITY_TYPE,
            ]);

        $this->add('getNameSettingForm')
            ->apiUrl('core/mobile/form/page.info_setting.name/:id')
            ->asGet();

        $this->add('getCategorySettingForm')
            ->apiUrl('core/mobile/form/page.info_setting.category_id/:id')
            ->asGet();

        $this->add('getProfileNameSettingForm')
            ->apiUrl('core/mobile/form/page.info_setting.profile_name/:id')
            ->asGet();

        $this->add('getAdditionalInformationSettingFrom')
            ->apiUrl('core/mobile/form/page.info_setting.additional_information/:id')
            ->asGet();

        $this->add('getTextSettingFrom')
            ->apiUrl('core/mobile/form/page.info_setting.text/:id')
            ->asGet();

        $this->add('getLocationSettingForm')
            ->apiUrl('core/mobile/form/page.info_setting.location/:id')
            ->asGet();

        $this->add('getExternalLinkForm')
            ->apiUrl('core/mobile/form/page.info_setting.external_link/:id')
            ->asGet();

        $this->add('getLandingPageSettingForm')
            ->apiUrl('core/mobile/form/page.info_setting.landing_page/:id')
            ->asGet();

        $this->add('getForTagFriendsInFeed')
            ->apiUrl('friend/mention')
            ->asGet()
            ->apiParams([
                'q'    => ':q',
                'view' => 'friend',
            ]);
    }
}
