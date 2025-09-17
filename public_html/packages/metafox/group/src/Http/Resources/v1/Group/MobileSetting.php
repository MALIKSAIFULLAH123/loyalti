<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\Browse\Scopes\Group\ViewScope;
use MetaFox\Group\Support\Facades\Group as GroupFacades;
use MetaFox\Platform\MetaFoxConstant;
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
        $this->add('viewItem')
            ->apiUrl('group/:id')
            ->urlParams(['id' => ':id',])
            ->apiParams(['invite_code' => ':invite_code']);

        $this->add('deleteItem')
            ->apiUrl('group/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('group::phrase.delete_confirm'),
                ]
            );

        $this->add('homePage')
            ->pageUrl('group');

        $this->add('joinGroup')
            ->apiUrl('group-member')
            ->asPost();

        $this->add('unjoinGroup')
            ->apiUrl('group-member/:id')
            ->asDelete()
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('group::phrase.un_join_confirm'),
            ]);

        $this->add('searchItem')
            ->apiUrl('/group')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'category_id' => ':category_id',
                'is_featured' => ':is_featured',
                'view'        => 'search',
            ])
            ->placeholder(__p('group::phrase.search_groups'));

        $this->add('updateAvatar')
            ->apiUrl('group/avatar/:id');

        $this->add('updateProfileCover')
            ->apiUrl('group/cover/:id')
            ->asPost();

        $this->add('removeProfileCover')
            ->apiUrl('group/cover/:id')
            ->asDelete()
            ->confirm([
                'message' => __p('photo::phrase.delete_confirm'),
            ]);

        $this->add('viewAll')
            ->apiUrl('group')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'category_id' => ':category_id',
                'is_featured' => ':is_featured',
                'view'        => ':view',
            ])
            ->apiRules(GroupFacades::getAllowApiRules());

        $this->add('viewSearchInGroup')
            ->apiUrl('search')
            ->apiParams([
                'owner_id'                    => ':id',
                'q'                           => ':q',
                'view'                        => ':item_type',
                'when'                        => ':when',
                'sort'                        => ':sort',
                'related_comment_friend_only' => ':related_comment_friend_only',
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

        $this->add('viewSearchSectionItemInGroup')
            ->apiUrl('search')
            ->apiParams([
                'owner_id'                    => ':id',
                'q'                           => ':q',
                'view'                        => ':item_type',
                'when'                        => ':when',
                'sort'                        => ':sort',
                'related_comment_friend_only' => ':related_comment_friend_only',
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

        $this->add('viewSearchSectionsInGroup')
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

        $this->add('viewOnOwner')
            ->apiUrl('group')
            ->apiParams([
                'user_id' => ':id',
                'view'    => ViewScope::VIEW_PROFILE,
            ]);

        $this->add('addItem')
            ->pageUrl('group/add')
            ->apiUrl('core/mobile/form/group.group.store');

        $this->add('editItem')
            ->pageUrl('group/settings/:id')
            ->apiUrl('')
            ->asGet();

        $this->add('getGroupInfoForm')
            ->apiUrl('core/mobile/form/group.group.info/:id')
            ->asGet();

        $this->add('getGroupPrivacyForm')
            ->apiUrl('core/mobile/form/group.group.privacy/:id')
            ->asGet();

        $this->add('getGroupAboutForm')
            ->apiUrl('core/mobile/form/group.group.about/:id')
            ->asGet();

        $this->add('sponsorItem')
            ->apiUrl('group/sponsor/:id')
            ->asPatch();

        /*
        * @deprecated Remove in 5.2.0
        */
        $this->add('featureItem')
            ->apiUrl('group/feature/:id')
            ->asPatch();

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('group/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('group/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('suggestItem')
            ->apiUrl('group/suggestion');

        $this->add('getGroupPermission')
            ->apiUrl('group/privacy/:id')
            ->asGet();

        $this->add('getGroupPermissionForm')
            ->apiUrl('core/mobile/form/group.group.permission/:id')
            ->asGet();

        $this->add('updateGroupPermission')
            ->apiUrl('group/privacy/:id')
            ->asPut();

        $this->add('acceptInvite')
            ->apiUrl('group-invite')
            ->asPut()
            ->apiParams(['group_id' => ':id', 'accept' => 1]);

        $this->add('declineInvite')
            ->apiUrl('group-invite')
            ->asPut()
            ->apiParams(['group_id' => ':id', 'accept' => 0]);

        $this->add('updatePendingMode')
            ->apiUrl('group/pending-mode/:id')
            ->asPatch()
            ->apiParams(['pending_mode' => ':pending_mode']);

        $this->add('viewPendingPost')
            ->apiUrl('feed')
            ->asGet()
            ->apiParams([
                'user_id'      => ':id',
                'status'       => MetaFoxConstant::ITEM_STATUS_PENDING,
                'has_pin_post' => 0,
            ]);

        $this->add('viewModerationRight')
            ->apiUrl('group/moderation-right/:id')
            ->asGet();

        $this->add('getModerationRightForm')
            ->apiUrl('core/mobile/form/group.group.moderation_right/:id')
            ->asGet();

        $this->add('updateModerationRight')
            ->apiUrl('group/moderation-right/:id')
            ->asPut();

        $this->add('inviteFriends')
            ->apiUrl('friend/invite-to-owner')
            ->asGet()
            ->apiParams(['q' => ':q', 'owner_id' => ':id', 'privacy_type' => Group::GROUP_MEMBERS]);

        $this->add('updateRuleConfirmation')
            ->apiUrl('group/confirm-rule')
            ->asPut()
            ->apiParams(['group_id' => ':id', 'is_rule_confirmation' => ':is_rule_confirmation']);

        $this->add('updateAnswerMembershipQuestion')
            ->apiUrl('group/confirm-answer-question')
            ->asPut()
            ->apiParams(['group_id' => ':id', 'is_answer_membership_question' => ':is_answer_membership_question']);

        $this->add('viewPendingGroups')
            ->apiUrl('group')
            ->apiParams([
                'view' => Browse::VIEW_PENDING,
            ]);

        $this->add('viewMyGroups')
            ->apiUrl('group')
            ->apiParams([
                'view' => 'my',
            ]);

        $this->add('viewFriendGroups')
            ->apiUrl('group')
            ->apiParams([
                'view' => Browse::VIEW_FRIEND,
            ]);

        $this->add('viewJoinedGroups')
            ->apiUrl('group')
            ->apiParams([
                'view' => 'joined',
            ]);

        $this->add('viewInvitedGroups')
            ->apiUrl('group')
            ->apiParams([
                'view' => 'invited',
            ]);

        $this->add('viewByType')
            ->apiUrl('group')
            ->apiParams([
                'type_id' => ':type_id',
            ]);

        $this->add('viewByCategory')
            ->apiUrl('group')
            ->apiParams([
                'category_id' => ':category_id',
            ]);

        $this->add('approveItem')
            ->apiUrl('group/approve/:id')
            ->asPatch();

        $this->add('searchGlobalGroup')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'       => 'group',
                'q'          => ':q',
                'is_hashtag' => ':is_hashtag',
            ]);

        $this->add('searchGlobalInGroup')
            ->apiUrl(apiUrl('search.group.index'))
            ->apiParams([
                'q'                           => ':q',
                'owner_id'                    => ':owner_id',
                'when'                        => ':when',
                'sort'                        => ':sort',
                'related_comment_friend_only' => ':related_comment_friend_only',
            ]);

        $this->add('viewMyPendingGroups')
            ->apiUrl('group')
            ->apiParams([
                'view' => Browse::VIEW_MY_PENDING,
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

        $this->add('generateInviteLink')
            ->apiUrl('group/invite-code')
            ->asPost()
            ->apiParams(['group_id' => ':id', 'refresh' => 0]);

        $this->add('shareOnGroupProfile')
            ->apiUrl('group/share-suggestion')
            ->apiParams([
                'q'     => ':q',
                'view'  => ViewScope::VIEW_JOINED,
                'limit' => 10,
            ]);

        $this->add('searchInOwner')
            ->apiUrl('group')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('group::phrase.search_groups'));

        $this->add('searchMember')
            ->apiUrl('search-group-member')
            ->apiParams([
                'q'         => ':q',
                'group_id'  => ':id',
                'view'      => ':view',
                'sort'      => ':sort',
                'sort_type' => ':sort_type',
            ]);

        $this->add('sponsorItemInFeed')
            ->apiUrl('group/sponsor-in-feed/:id')
            ->asPatch();

        $this->add('viewSimilar')
            ->apiUrl('group/similar')
            ->apiParams([
                'group_id' => ':id',
                'limit'    => 5,
                'when'     => ':when',
                'sort'     => ':sort',
            ]);

        $this->add('getForTagMembersInFeed')
            ->asGet()
            ->apiUrl('friend/mention')
            ->apiParams([
                'q'              => ':q',
                'owner_id'       => ':owner_id',
                'is_member_only' => 1,
            ]);

        $this->add('getForMentionInFeed')
            ->asGet()
            ->apiUrl('friend/mention')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':owner_id',
            ]);

        $this->add('groupInfo')
            ->apiUrl('group-info/:id')
            ->pageUrl('group-info/:id');

        $this->add('getGroupInfoSettings')
            ->apiUrl('group/info-setting/:id')
            ->asGet();

        $this->add('getGroupAboutSettings')
            ->apiUrl('group/about-setting/:id')
            ->asGet();

        $this->add('getNameSettingForm')
            ->apiUrl('core/mobile/form/group.info_setting.name/:id')
            ->asGet();

        $this->add('getCategorySettingForm')
            ->apiUrl('core/mobile/form/group.info_setting.category_id/:id')
            ->asGet();

        $this->add('getProfileNameSettingForm')
            ->apiUrl('core/mobile/form/group.info_setting.profile_name/:id')
            ->asGet();

        $this->add('getAdditionalInformationSettingFrom')
            ->apiUrl('core/mobile/form/group.info_setting.additional_information/:id')
            ->asGet();

        $this->add('getTextSettingFrom')
            ->apiUrl('core/mobile/form/group.info_setting.text/:id')
            ->asGet();

        $this->add('getLocationSettingForm')
            ->apiUrl('core/mobile/form/group.info_setting.location/:id')
            ->asGet();

        $this->add('getLandingPageSettingForm')
            ->apiUrl('core/mobile/form/group.info_setting.landing_page/:id')
            ->asGet();
    }
}
