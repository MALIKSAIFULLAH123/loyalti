<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Page\Jobs\CleanUpDeletedPageJob;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageClaim;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Notifications\ApproveNewPostNotification;
use MetaFox\Page\Notifications\ApproveRequestClaimNotification;
use MetaFox\Page\Notifications\ClaimNotification;
use MetaFox\Page\Notifications\LikePageNotification;
use MetaFox\Page\Notifications\PageApproveNotification;
use MetaFox\Page\Notifications\PageInvite as PageInviteNotification;
use MetaFox\Page\Notifications\UpdateInformationNotification;
use MetaFox\Page\Policies\CategoryPolicy;
use MetaFox\Page\Policies\PageClaimPolicy;
use MetaFox\Page\Policies\PageMemberPolicy;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getActivityTypes(): array
    {
        return [
            [
                'type'                            => Page::PAGE_UPDATE_PROFILE_ENTITY_TYPE,
                'entity_type'                     => Page::ENTITY_TYPE,
                'is_active'                       => true,
                'title'                           => 'page::phrase.page_upload_avatar_type',
                'description'                     => 'page_user_name_updated_their_profile_photo',
                'is_system'                       => 0,
                'can_comment'                     => true,
                'can_like'                        => true,
                'can_share'                       => true,
                'can_edit'                        => false,
                'can_create_feed'                 => true,
                'prevent_delete_feed_items'       => true,
                'prevent_display_tag_on_headline' => true,
            ],
            [
                'type'                            => Page::PAGE_UPDATE_COVER_ENTITY_TYPE,
                'entity_type'                     => Page::ENTITY_TYPE,
                'is_active'                       => true,
                'title'                           => 'page::phrase.page_update_cover_photo_type',
                'description'                     => 'page_user_name_updated_their_cover_photo',
                'is_system'                       => 0,
                'can_comment'                     => true,
                'can_like'                        => true,
                'can_share'                       => true,
                'can_edit'                        => false,
                'can_create_feed'                 => true,
                'prevent_delete_feed_items'       => true,
                'prevent_display_tag_on_headline' => true,
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Page::ENTITY_TYPE => [
                'view'                        => UserRole::LEVEL_GUEST,
                'create'                      => UserRole::LEVEL_REGISTERED,
                'update'                      => UserRole::LEVEL_REGISTERED,
                'delete'                      => UserRole::LEVEL_REGISTERED,
                'moderate'                    => UserRole::LEVEL_STAFF,
                'feature'                     => [
                    'roles'     => UserRole::LEVEL_STAFF,
                    'is_public' => false,
                ],
                'purchase_feature'            => [
                    'roles'     => UserRole::LEVEL_PAGE,
                    'is_public' => false,
                ],
                'approve'                     => UserRole::LEVEL_STAFF,
                'claim'                       => UserRole::LEVEL_STAFF,
                'share'                       => UserRole::LEVEL_REGISTERED,
                'report'                      => UserRole::LEVEL_REGISTERED,
                'auto_approved'               => UserRole::LEVEL_REGISTERED,
                'upload_cover'                => UserRole::LEVEL_REGISTERED,
                'sponsor'                     => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'sponsor_free'                => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
                'auto_publish_sponsored_item' => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
            ],
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            Page::ENTITY_TYPE => [
                'flood_control'                                       => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'quota_control'                                       => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'quota_control_' . MetaFoxConstant::TIMEFRAME_DAILY   => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'action'  => 'quota_control',
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'quota_control_' . MetaFoxConstant::TIMEFRAME_MONTHLY => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'action'  => 'quota_control',
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
            ],
        ];
    }

    public function getUserPrivacyResource(): array
    {
        return [
            Page::ENTITY_TYPE => [
                'core.view_publish_date',
                'core.view_admins' => [
                    'phrase' => 'page::phrase.user_privacy.who_can_view_admins',
                ],
            ],
        ];
    }

    public function getProfileMenu(): array
    {
        return [
            Page::ENTITY_TYPE => [
                'phrase'  => 'page::phrase.pages',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'models.notify.deleting'                       => [
                ModelDeletingListener::class,
            ],
            'models.notify.deleted'                        => [
                ModelDeletedListener::class,
            ],
            'models.notify.updated'                        => [
                ModelUpdatedListener::class,
            ],
            'page.update_cover'                            => [
                UpdatePageCover::class,
            ],
            'page.update_avatar'                           => [
                UpdatePageAvatar::class,
            ],
            'page.get_user_preview'                        => [
                UserPreviewListener::class,
            ],
            'page.get_search_resource'                     => [
                GetSearchResourceListener::class,
            ],
            'page.get_privacy_for_setting'                 => [
                PrivacyForSetting::class,
            ],
            'user.get_shortcut_type'                       => [
                GetShortcutTypeListener::class,
            ],
            'parseRoute'                                   => [
                PageRouteListener::class,
            ],
            'friend.mention.builder'                       => [
                FriendMentionBuilderListener::class,
            ],
            'core.parse_content'                           => [
                ParseFeedContentListener::class,
            ],
            'core.mention.pattern'                         => [
                MentionPatternContentListener::class,
            ],
            'friend.mention.members.builder'               => [
                FriendMemberMentionBuilderListener::class,
            ],
            'friend.mention.extra_info'                    => [
                FriendMentionExtraInfoListener::class,
            ],
            'friend.invite.members'                        => [
                GetIdsUserInviteListener::class,
            ],
            'activity.share.data_preparation'              => [
                SharedDataPreparationListener::class,
            ],
            'activity.share.rules'                         => [
                ShareRuleListener::class,
            ],
            'search.owner_options'                         => [
                SearchOwnerOptionListener::class,
            ],
            'core.collect_total_items_stat'                => [
                CollectTotalItemsStatListener::class,
            ],
            'activity.get_privacy_detail_on_owner'         => [
                GetPrivacyDetailOnOwnerListener::class,
            ],
            'friend.mention.notifiables'                   => [
                FriendMentionNotifiableListener::class,
            ],
            'user.deleting'                                => [
                PageDeletingListener::class,
            ],
            'user.role.downgrade'                          => [
                UserRoleDowngradeListener::class,
            ],
            'activity.notify.approved_new_post_in_owner'   => [
                ApprovedNewPostListener::class,
            ],
            'models.notify.created'                        => [
                ModelCreatedListener::class,
            ],
            'models.notify.approved'                       => [
                ModelApprovedListener::class,
            ],
            'user.verified'                                => [
                UserVerifiedListener::class,
            ],
            'notification.new_post_to_follower'            => [
                ModelApprovedListener::class,
            ],
            'notification.delete_notification_to_follower' => [
                DeleteNotifyApprovedNewPostListener::class,
            ],
            'livestreaming.build_post_to'                  => [
                LiveStreamPostToListener::class,
            ],
            'photo.after_delete_photo'                     => [
                PhotoAfterDeleteListener::class,
            ],
            'user.get_mentions'                            => [
                UserGetMentionsListener::class,
            ],
            'core.filter_mention_users'                    => [
                FilterMentionUsersListener::class,
            ],
            'friend.invite.members.builder'                => [
                FriendInviteMemberBuilderListener::class,
            ],
            'friend.mention.transform_data_after'          => [
                FriendMentionTransformAfterListener::class,
            ],
            'importer.completed'                           => [
                ImporterCompleted::class,
            ],
            'friend.invite.owner.builder'                  => [
                GetInviteBuilderListener::class,
            ],
            'core.get_extra_tag_scope_post_as'             => [
                GetExtraTagScopePostAsListener::class,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'default_item_privacy'                 => ['value' => MetaFoxPrivacy::EVERYONE],
            'admin_in_charge_of_page_claims'       => ['value' => []],
            'display_profile_photo_within_gallery' => ['value' => true],
            'display_cover_photo_within_gallery'   => ['value' => true],
            'default_category'                     => ['value' => 1],
            'minimum_name_length'                  => ['value' => MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH],
            'maximum_name_length'                  => ['value' => 64],
            'page.purchase_sponsor_price'          => [
                'value'     => '',
                'is_public' => false,
            ],
            'auto_follow_pages_on_signup'          => ['value' => [], 'type' => MetaFoxDataType::ARRAY],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'page_invite',
                'module_id'  => 'page',
                'handler'    => PageInviteNotification::class,
                'title'      => 'page::phrase.page_invite_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 15,
            ],
            [
                'type'       => 'claim_page',
                'module_id'  => 'page',
                'handler'    => ClaimNotification::class,
                'title'      => 'page::phrase.claim_page_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 0,
                'channels'   => ['database', 'mail', 'webpush'],
                'ordering'   => 16,
            ],
            [
                'type'       => 'like_page',
                'module_id'  => 'page',
                'handler'    => LikePageNotification::class,
                'title'      => 'page::phrase.like_page_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 0,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 17,
            ],
            [
                'type'       => 'page_approve_notification',
                'module_id'  => 'page',
                'handler'    => PageApproveNotification::class,
                'title'      => 'page::phrase.approve_page_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 18,
            ],
            [
                'type'       => 'approve_claim_page',
                'module_id'  => 'page',
                'handler'    => ApproveRequestClaimNotification::class,
                'title'      => 'page::phrase.approve_claim_page_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 19,
            ],
            [
                'type'       => 'page_new_post',
                'module_id'  => 'page',
                'handler'    => ApproveNewPostNotification::class,
                'title'      => 'page::phrase.page_new_post_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
            ],
            [
                'type'       => 'page_update_info',
                'module_id'  => 'page',
                'handler'    => UpdateInformationNotification::class,
                'title'      => 'page::phrase.update_info_page_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Page::class       => PagePolicy::class,
            Category::class   => CategoryPolicy::class,
            PageMember::class => PageMemberPolicy::class,
            PageClaim::class  => PageClaimPolicy::class,
        ];
    }

    public function getSiteStatContent(): ?array
    {
        return [
            Page::ENTITY_TYPE => ['icon' => 'ico-flag-waving-o'],
            'pending_page'    => [
                'icon' => 'ico-clock-o',
                'to'   => '/page/page/browse?view=' . Browse::VIEW_PENDING,
            ],
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(resolve(CleanUpDeletedPageJob::class))->everySixHours()->withoutOverlapping();
    }

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return ['page', 'page_category'];
    }

    /**
     * @return array<int, mixed>
     */
    public function getAdMobPages(): array
    {
        return [
            [
                'path' => '/page',
                'name' => 'page::phrase.ad_mob_page_home_page',
            ],
            [
                'path' => '/page/:id',
                'name' => 'page::phrase.ad_mob_page_detail_page',
            ],
        ];
    }
}
