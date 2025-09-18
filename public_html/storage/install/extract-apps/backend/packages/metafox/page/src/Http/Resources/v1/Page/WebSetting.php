<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Http\Resources\v1\Page;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Support\Browse\Scopes\Page\ViewScope;
use MetaFox\Page\Support\Facade\Page as PageFacade;
use MetaFox\Platform\Resource\WebSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * class WebSetting.
 *
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('page');

        $this->add('searchItem')
            ->pageUrl('page/search')
            ->pageParams(['view' => Browse::VIEW_SEARCH])
            ->placeholder(__p('page::phrase.search_pages'));

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
            ->apiRules(PageFacade::getAllowApiRules());

        $this->add('viewItem')
            ->apiUrl('page/:id')
            ->pageUrl('page/:id');

        $this->add('deleteItem')
            ->apiUrl('page/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('page::phrase.delete_confirm'),
                ]
            )
            ->pageUrl('page/all');

        $this->add('addItem')
            ->pageUrl('page/add')
            ->apiUrl('core/form/page.store');

        $this->add('editItem')
            ->pageUrl('page/settings/:id')
            ->apiUrl('');

        $this->add('sponsorItem')
            ->asPatch()
            ->apiUrl('page/sponsor/:id');

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

        $this->add('getPagePermission')
            ->apiUrl('page/privacy/:id')
            ->asGet();

        $this->add('updatePagePermission')
            ->apiUrl('page/privacy/:id')
            ->asPut();

        $this->add('inviteFriends')
            ->apiUrl('friend/invite-to-owner')
            ->asGet()
            ->apiParams(['q' => ':q', 'owner_id' => ':id', 'privacy_type' => Page::PAGE_MEMBERS]);

        $this->add('getShareOnPageSuggestion')
            ->apiUrl('page/share-suggestion')
            ->apiParams(['view' => ViewScope::VIEW_LIKED, 'limit' => 10]);

        $this->add('viewSimilar')
            ->apiUrl('page/similar')
            ->apiParams([
                'page_id' => ':id',
                'limit'   => 3,
            ]);

        $this->add('approveItem')
            ->apiUrl('page/approve/:id')
            ->asPatch();

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
                'q'    => ['truthy', 'q'],
                'view' => ['truthy', 'view'],
                'when' => [
                    'includes',
                    'when',
                    [
                        'this_month',
                        'this_week',
                        'today',
                        'all',
                    ],
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
                'q'    => ['truthy', 'q'],
                'view' => ['truthy', 'view'],
                'when' => [
                    'includes',
                    'when',
                    [
                        'this_month',
                        'this_week',
                        'today',
                        'all',
                    ],
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
                'q'        => ['truthy', 'q'],
                'owner_id' => ['truthy', 'owner_id'],
                'when'     => [
                    'includes',
                    'when',
                    [
                        'this_month',
                        'this_week',
                        'today',
                        'all',
                    ],
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
                'related_comment_friend_only' => ['truthy', 'related_comment_friend_only'],
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

        $this->add('shareOnPageProfile');

        $this->add('getInfoSettings')
            ->apiUrl('page/info-setting/:id')
            ->asGet();

        $this->add('getAboutSettings')
            ->apiUrl('page/about-setting/:id')
            ->asGet();

        $this->add('getPageSchedulePosts')
            ->apiUrl('feed-schedule')
            ->asGet()
            ->apiParams([
                'entity_id'   => ':id',
                'entity_type' => Page::ENTITY_TYPE,
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

        $this->add('getForTagFriendsInFeed')
            ->apiUrl('friend/mention')
            ->asGet()
            ->apiParams([
                'q'    => ':q',
                'view' => 'friend',
            ]);

        $this->add('getNameSettingForm')
            ->apiUrl('core/form/page.info_setting.name/:id')
            ->asGet();

        $this->add('getCategorySettingForm')
            ->apiUrl('core/form/page.info_setting.category_id/:id')
            ->asGet();

        $this->add('getProfileNameSettingForm')
            ->apiUrl('core/form/page.info_setting.profile_name/:id')
            ->asGet();

        $this->add('getAdditionalInformationSettingFrom')
            ->apiUrl('core/form/page.info_setting.additional_information/:id')
            ->asGet();

        $this->add('getTextSettingFrom')
            ->apiUrl('core/form/page.info_setting.text/:id')
            ->asGet();

        $this->add('getLocationSettingForm')
            ->apiUrl('core/form/page.info_setting.location/:id')
            ->asGet();

        $this->add('getLandingPageSettingForm')
            ->apiUrl('core/form/page.info_setting.landing_page/:id')
            ->asGet();

        $this->add('getExternalLinkForm')
            ->apiUrl('core/form/page.info_setting.external_link/:id')
            ->asGet();
    }
}
