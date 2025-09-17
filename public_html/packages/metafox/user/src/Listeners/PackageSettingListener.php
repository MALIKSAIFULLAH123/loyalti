<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Events\RefreshTokenCreated;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;
use MetaFox\User\Jobs\CleanUpDeletedUserJob;
use MetaFox\User\Jobs\ExpiredUserBanJob;
use MetaFox\User\Jobs\InactiveProcessingJob;
use MetaFox\User\Jobs\MaintainPendingVerification;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Models\UserRelationHistory;
use MetaFox\User\Models\UserShortcut;
use MetaFox\User\Notifications\DirectUpdatedPassword;
use MetaFox\User\Notifications\DoneExportProcessNotification;
use MetaFox\User\Notifications\NewPostTimeline;
use MetaFox\User\Notifications\ProcessMailingInactiveUser;
use MetaFox\User\Notifications\ProfileUpdatedByAdmin;
use MetaFox\User\Notifications\ResetPasswordTokenNotification;
use MetaFox\User\Notifications\UserApproveNotification;
use MetaFox\User\Notifications\UserPendingApprovalNotification;
use MetaFox\User\Notifications\UserRelationWithUserNotification;
use MetaFox\User\Notifications\WelcomeNewMember;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Policies\UserProfilePolicy;
use MetaFox\User\Policies\UserRelationHistoryPolicy;
use MetaFox\User\Policies\UserShortcutPolicy;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;
use MetaFox\User\Support\User as UserSupport;
use MetaFox\User\Support\UserBirthday;

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 *
 * @ignore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    /**
     * @return string[]
     */
    public function getCaptchaRules(): array
    {
        return [
            'user_signup',
            'user_login',
            'forgot_password',
        ];
    }

    public function getActivityTypes(): array
    {
        return [
            [
                'type'            => User::ENTITY_TYPE,
                'entity_type'     => User::ENTITY_TYPE,
                'is_active'       => true,
                'title'           => 'user::phrase.user_registered_type',
                'description'     => 'user::phrase.has_registered',
                'is_system'       => 0,
                'can_comment'     => false,
                'can_like'        => false,
                'can_share'       => false,
                'can_edit'        => false,
                'can_create_feed' => true,
                'allow_comment'   => false,
            ],
            [
                'type'                            => User::USER_UPDATE_AVATAR_ENTITY_TYPE,
                'entity_type'                     => User::ENTITY_TYPE,
                'is_active'                       => true,
                'title'                           => 'user::phrase.user_update_avatar_type',
                'description'                     => 'user::phrase.user_name_updated_their_profile_picture',
                'is_system'                       => 0,
                'can_comment'                     => true,
                'can_like'                        => true,
                'can_share'                       => false,
                'can_edit'                        => false,
                'can_create_feed'                 => true,
                'prevent_delete_feed_items'       => true,
                'prevent_display_tag_on_headline' => true,
                'params'                          => [
                    'gender'     => 'user_entity.possessive_gender',
                    'isAuthUser' => 'is_auth_user',
                ],
            ],
            [
                'type'                            => User::USER_UPDATE_COVER_ENTITY_TYPE,
                'entity_type'                     => User::ENTITY_TYPE,
                'is_active'                       => true,
                'title'                           => 'user::phrase.user_update_cover_photo_type',
                'description'                     => 'user::phrase.user_name_updated_their_cover_photo',
                'is_system'                       => 0,
                'can_comment'                     => true,
                'can_like'                        => true,
                'can_share'                       => true,
                'can_edit'                        => false,
                'can_create_feed'                 => true,
                'prevent_delete_feed_items'       => true,
                'prevent_display_tag_on_headline' => true,
                'params'                          => [
                    'gender'     => 'user_entity.possessive_gender',
                    'isAuthUser' => 'is_auth_user',
                ],
            ],
            [
                'type'                            => User::USER_AVATAR_SIGN_UP,
                'entity_type'                     => User::ENTITY_TYPE,
                'is_active'                       => true,
                'title'                           => 'user::phrase.user_upload_signup_avatar_type',
                'description'                     => 'user::phrase.user_name_updated_their_profile_picture',
                'is_system'                       => 0,
                'can_comment'                     => false,
                'can_like'                        => false,
                'can_share'                       => false,
                'can_edit'                        => false,
                'can_create_feed'                 => false,
                'prevent_delete_feed_items'       => true,
                'prevent_display_tag_on_headline' => true,
                'params'                          => [
                    'gender'     => 'user_entity.possessive_gender',
                    'isAuthUser' => 'is_auth_user',
                ],
            ],
            [
                'type'            => User::USER_UPDATE_INFORMATION_ENTITY_TYPE,
                'entity_type'     => User::ENTITY_TYPE,
                'is_active'       => true,
                'title'           => 'user::phrase.user_update_information_type',
                'description'     => 'user::phrase.user_name_updated_their_information',
                'is_system'       => 0,
                'can_comment'     => false,
                'can_like'        => false,
                'can_share'       => false,
                'can_edit'        => false,
                'can_create_feed' => true,
                'action_on_feed'  => true,
                'params'          => [
                    'gender'     => 'user_entity.possessive_gender',
                    'isAuthUser' => 'is_auth_user',
                ],
                'hidden_keys'     => ['can_comment', 'can_like', 'can_share'],
            ],
            [
                'type'            => User::USER_UPDATE_RELATIONSHIP_ENTITY_TYPE,
                'entity_type'     => UserRelationHistory::ENTITY_TYPE,
                'is_active'       => true,
                'title'           => 'user::phrase.user_update_relationship_type',
                'description'     => 'user_name_updated_their_relationship',
                'is_system'       => 0,
                'can_comment'     => true,
                'can_like'        => true,
                'can_share'       => true,
                'can_edit'        => false,
                'can_create_feed' => true,
                'action_on_feed'  => true,
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            User::ENTITY_TYPE         => [
                'view'                      => UserRole::LEVEL_GUEST,
                'update'                    => UserRole::LEVEL_REGISTERED,
                'delete'                    => UserRole::LEVEL_REGISTERED,
                'moderate'                  => UserRole::LEVEL_STAFF,
                'can_block_other_members'   => UserRole::LEVEL_REGISTERED,
                'can_be_blocked_by_others'  => [UserRole::LEVEL_REGISTERED],
                'report'                    => UserRole::LEVEL_REGISTERED,
                'feature'                   => [
                    'roles'     => UserRole::LEVEL_STAFF,
                    'is_public' => false,
                ],
                'purchase_feature'          => [
                    'roles'     => UserRole::LEVEL_PAGE,
                    'is_public' => false,
                ],
                'can_override_user_privacy' => UserRole::LEVEL_ADMINISTRATOR,
            ],
            UserShortcut::ENTITY_TYPE => [
                'view'     => UserRole::LEVEL_REGISTERED,
                'moderate' => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            User::class                => UserPolicy::class,
            UserShortcut::class        => UserShortcutPolicy::class,
            UserProfile::class         => UserProfilePolicy::class,
            UserRelationHistory::class => UserRelationHistoryPolicy::class,
        ];
    }

    public function getEvents(): array
    {
        return [
            'packages.installed'                   => [PackageInstalledListener::class],
            'models.notify.creating'               => [ModelCreatingListener::class],
            'models.notify.created'                => [ModelCreatedListener::class],
            'models.notify.deleted'                => [ModelDeletedListener::class],
            'models.notify.updated'                => [ModelUpdatedListener::class],
            'user.get_user_preview'                => [
                UserPreviewListener::class,
            ],
            'user.get_mentions'                    => [
                UserGetMentions::class,
            ],
            AccessTokenCreated::class              => [
                AccessTokenCreatedListener::class,
            ],
            RefreshTokenCreated::class             => [
                RefreshTokenCreatedListener::class,
            ],
            'user.get_search_resource'             => [
                GetSearchResourceListener::class,
            ],
            'user.get_privacy_for_setting'         => [
                PrivacyForSetting::class,
            ],
            'parseRoute'                           => [
                ProfileRouteListener::class,
                SettingRouteListener::class,
            ],
            'feed.composer.notification'           => [
                FeedComposerNotificationListener::class,
            ],
            'user.update_cover'                    => [
                UpdateProfileCoverListener::class,
            ],
            'user.update_avatar'                   => [
                UpdateProfileAvatarListener::class,
            ],
            'user.user_blocked'                    => [
                BlockedListener::class,
            ],
            'user.user_unblocked'                  => [
                UnBlockedListener::class,
            ],
            'user.check_value_setting_by_name'     => [
                CheckUserValueSettingByNameListener::class,
            ],
            'user.permissions.extra'               => [
                UserExtraPermissionListener::class,
            ],
            'friend.mention.extra_info'            => [
                FriendMentionExtraInfoListener::class,
            ],
            'user.registration.extra_field.rules'  => [
                UserRegistrationExtraFieldsRulesListener::class,
            ],
            'user.registration.extra_field.create' => [
                UserRegistrationExtraFieldsCreateListener::class,
            ],
            'search.owner_options'                 => [
                SearchOwnerOptionListener::class,
            ],
            'user.registered'                      => [
                UserRegisteredListener::class,
            ],
            'user.verified'                        => [
                UserVerifiedListener::class,
            ],
            'validation.unique_slug'               => [
                UniqueSlugListener::class,
            ],
            'user.signed_in'                       => [
                UserSignedInListener::class,
            ],
            'core.collect_total_items_stat'        => [
                CollectTotalItemsStatListener::class,
            ],
            'like.owner.notification'              => [
                LikeNotificationListener::class,
            ],
            'user.deleting'                        => [
                UserDeletingListener::class,
            ],
            'user.logout'                          => [
                UserLogoutListener::class,
            ],
            'user.role.deleted'                    => [
                UserRoleDeletedListener::class,
            ],
            'activity.feed.deleted'                => [
                FeedDeletedListener::class,
            ],
            'photo.after_delete_photo'             => [
                PhotoAfterDeleteListener::class,
            ],
            'activity.get_privacy_detail_on_owner' => [
                GetPrivacyDetailOnOwnerListener::class,
            ],
            'importer.completed'                   => [
                ImporterCompleted::class,
            ],
            'core.parse_redirect_route'            => [
                ParseRedirectRouteListener::class,
            ],
            'models.actions.pending'               => [
                ModelPendingActionListener::class,
            ],
        ];
    }

    public function getUserPrivacy(): array
    {
        return [
            'profile.view_profile'  => [
                'phrase'  => 'user::phrase.user_privacy.who_can_view_your_profile_page',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
            'profile.profile_info'  => [
                'phrase'  => 'user::phrase.user_privacy.who_can_view_the_info_tab_on_your_profile_page',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
            'profile.basic_info'    => [
                'phrase'  => 'user::phrase.user_privacy.who_can_view_your_basic_info',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
            'profile.view_location' => [
                'phrase'  => 'user::phrase.user_privacy.who_can_view_your_location',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
            'user.can_i_be_tagged'  => [
                'phrase'  => 'user::phrase.user_privacy.who_can_tag_me_in_written_context',
                'default' => MetaFoxPrivacy::FRIENDS,
            ],
        ];
    }

    public function getUserPrivacyResource(): array
    {
        return [
            User::ENTITY_TYPE => [
                'profile.view_profile',
                'profile.profile_info',
                'profile.basic_info',
                'profile.view_location',
                'user.can_i_be_tagged' => [
                    'default' => MetaFoxPrivacy::FRIENDS,
                    'list'    => [
                        MetaFoxPrivacy::FRIENDS,
                        MetaFoxPrivacy::ONLY_ME,
                    ],
                ],
            ],
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(resolve(ExpiredUserBanJob::class))->hourly()->withoutOverlapping();
        $schedule->job(resolve(MaintainPendingVerification::class))->hourly()->withoutOverlapping();
        $schedule->job(resolve(CleanUpDeletedUserJob::class))->everySixHours()->withoutOverlapping();
        $schedule->job(resolve(InactiveProcessingJob::class))->everyFiveMinutes()->withoutOverlapping();
    }

    public function getSiteSettings(): array
    {
        return [
            'on_register_user_group'                    => ['value' => UserRole::NORMAL_USER_ID],
            'allow_user_registration'                   => ['value' => true],
            'signup_repeat_password'                    => ['value' => false],
            // 'multi_step_registration_form'               => ['value' => false],
            'new_user_terms_confirmation'               => ['value' => true],
            'verify_after_changing_email'               => ['value' => false],
            'verify_after_changing_phone_number'        => ['value' => false],
            // 'display_user_online_status'                 => ['value' => false],
            // 'profile_use_id'                             => ['value' => false],
            // 'login_type'                                 => ['value' => 'email'],
            'date_of_birth_start'                       => ['value' => 1900],
            'date_of_birth_end'                         => ['value' => Carbon::now()->year],
            // 'display_or_full_name'                       => ['value' => 'full_name'],
            // 'check_promotion_system'                     => ['value' => true],
            // 'enable_user_tooltip'                        => ['value' => true],
            'brute_force_attempts_count'                => ['value' => 5],
            'brute_force_time_check'                    => ['value' => 0],
            'brute_force_cool_down'                     => ['value' => 15],
            'enable_sms_registration'                   => ['value' => false],
            'enable_auto_login_after_registration'      => ['value' => false],
            'enable_phone_number_registration'          => ['value' => false],
            'enable_opt_in_agreement'                   => ['value' => false],
            'verify_email_at_signup'                    => ['value' => false],
            'verification_timeout'                      => ['value' => 60],
            'days_for_delete_pending_user_verification' => ['value' => 0],
            'resend_verification_delay_time'            => ['value' => 15],
            'maximum_length_for_full_name'              => ['value' => 25],
            'minimum_length_for_password'               => ['value' => 4],
            'maximum_length_for_password'               => ['value' => 30],
            'default_birthday_privacy'                  => ['value' => UserBirthday::DATE_OF_BIRTH_SHOW_ALL],
            'user_dob_month_day_year'                   => ['value' => 'F j, Y'],
            'user_dob_month_day'                        => ['value' => 'F j'],
            // 'split_full_name'                            => ['value' => false],
            'redirect_after_login'                      => ['value' => ''],
            // 'redirect_after_signup'                      => ['value' => ''],
            'redirect_after_logout'                     => ['value' => ''],
            // 'disable_store_last_user'                    => ['value' => false],
            'enable_feed_user_update_relationship'      => ['value' => true],
            // 'cache_recent_logged_in'                     => ['value' => 0],
            'min_length_for_username'                   => ['value' => 5],
            'max_length_for_username'                   => ['value' => 25],
            'enable_feed_user_update_profile'           => ['value' => false],
            'validate_full_name'                        => ['value' => true],
            'approve_users'                             => ['value' => false],
            'force_user_to_upload_on_sign_up'           => ['value' => false],
            'on_signup_new_friend'                      => ['value' => [], 'type' => MetaFoxDataType::ARRAY],
            'redirect_after_signup'                     => ['value' => ''],
            'on_register_privacy_setting'               => ['value' => MetaFoxPrivacy::MEMBERS],
            'available_name_field_on_sign_up'           => ['value' => UserSupport::DISPLAY_BOTH],
            'captcha_on_login'                          => ['value' => false],
            // 'hide_main_menu'                             => ['value' => false],
            // 'invite_only_community'                      => ['value' => false],
            'required_strong_password'                  => ['value' => false],
            'browse_user_default_order'                 => ['value' => SortScope::SORT_FULL_NAME],
            'force_user_to_reenter_email'               => ['value' => false],
            'shorter_reset_password_routine'            => ['value' => false],
            'user_role_filter_exclude'                  => ['value' => [], 'is_public' => false],
            'user_profile_default_theme_type'           => ['value' => 'auto', 'is_public' => false],
            'passport_token_expire_time'                => [
                'config_name' => 'auth.passport_token_expire_time',
                'value'       => 360,
            ],
            'force_frequent_password_change'            => ['value' => false, 'is_public' => false],
            'force_frequent_password_change_period'     => ['value' => 90],
            'force_password_history_check'              => ['value' => false, 'is_public' => false],
            'number_of_password_history'                => ['value' => 5],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'request_reset_password_token',
                'title'      => 'user::phrase.new_password_requested_notification_type',
                'handler'    => ResetPasswordTokenNotification::class,
                'module_id'  => 'user',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 0,
                'channels'   => ['mail'],
                'ordering'   => 20,
            ],
            [
                'type'       => 'new_password_updated',
                'title'      => 'user::phrase.new_password_updated_notification_type',
                'handler'    => DirectUpdatedPassword::class,
                'module_id'  => 'user',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 0,
                'channels'   => ['mail'],
                'ordering'   => 21,
            ],
            [
                'type'       => 'new_post_timeline',
                'title'      => 'user::phrase.new_post_timeline_notification_type',
                'handler'    => NewPostTimeline::class,
                'module_id'  => 'user',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 22,
            ],
            [
                'type'       => 'profile_updated_by_admin',
                'title'      => 'user::phrase.profile_updated_by_admin_notification_type',
                'handler'    => ProfileUpdatedByAdmin::class,
                'module_id'  => 'user',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 23,
            ],
            [
                'type'       => 'user_approve_notification',
                'module_id'  => 'user',
                'handler'    => UserApproveNotification::class,
                'title'      => 'user::phrase.user_approved_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 0,
                'channels'   => ['mail'],
                'ordering'   => 24,
            ],
            [
                'type'       => 'user_welcome',
                'module_id'  => 'user',
                'handler'    => WelcomeNewMember::class,
                'title'      => 'user::phrase.user_welcome_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 0,
                'channels'   => ['mail'],
                'ordering'   => 26,
            ],
            [
                'type'       => 'process_mailing_inactive_user',
                'module_id'  => 'user',
                'handler'    => ProcessMailingInactiveUser::class,
                'title'      => 'user::phrase.process_mailing_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 0,
                'channels'   => ['mail'],
                'ordering'   => 27,
            ],
            [
                'type'       => 'user_relation_with_user',
                'module_id'  => 'user',
                'handler'    => UserRelationWithUserNotification::class,
                'title'      => 'user::phrase.user_relation_with_user_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 28,
            ],
            [
                'type'       => 'user_pending_approval_notification',
                'module_id'  => 'user',
                'handler'    => UserPendingApprovalNotification::class,
                'title'      => 'user::mail.user_pending_approval_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 0,
                'channels'   => ['mail'],
                'ordering'   => 28,
            ],
            [
                'type'       => 'done_export_process_user',
                'module_id'  => 'user',
                'handler'    => DoneExportProcessNotification::class,
                'title'      => 'user::mail.done_export_process_user_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 0,
                'channels'   => ['mail'],
                'ordering'   => 28,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getUserValues(): array
    {
        return [
            User::ENTITY_TYPE => [
                'user_profile_date_of_birth_format'  => [
                    'default_value' => UserBirthday::DATE_OF_BIRTH_SHOW_ALL,
                    'ordering'      => 1,
                ],
                'user_auto_add_tagger_post'          => [
                    'default_value' => UserSupport::AUTO_APPROVED_TAGGER_POST,
                    'ordering'      => 2,
                ],
                UserSupport::AUTO_PLAY_VIDEO_SETTING => [
                    'default_value' => 1,
                    'ordering'      => 3,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getActivityPointSettings(): array
    {
        return [
            'metafox/user' => [
                [
                    'name'               => User::ENTITY_TYPE . '.sign_up',
                    'action'             => 'sign_up',
                    'module_id'          => 'user',
                    'package_id'         => 'metafox/user',
                    'description_phrase' => 'user::activitypoint.setting_sign_up_description',
                    'extra'              => [
                        'disabled' => ['max_earned', 'period'],
                    ],
                ],
                [
                    'name'               => User::ENTITY_TYPE . '.sign_in',
                    'action'             => 'sign_in',
                    'module_id'          => 'user',
                    'package_id'         => 'metafox/user',
                    'description_phrase' => 'user::activitypoint.setting_sign_in_description',
                ],
                [
                    'name'               => User::ENTITY_TYPE . '.new_profile_photo',
                    'action'             => 'new_profile_photo',
                    'module_id'          => 'user',
                    'package_id'         => 'metafox/user',
                    'description_phrase' => 'user::activitypoint.setting_new_profile_photo_description',
                ],
                [
                    'name'               => User::ENTITY_TYPE . '.new_profile_cover',
                    'action'             => 'new_profile_cover',
                    'module_id'          => 'user',
                    'package_id'         => 'metafox/user',
                    'description_phrase' => 'user::activitypoint.setting_new_profile_cover_description',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getActivityPointActions(): array
    {
        return [
            'metafox/user' => [
                [
                    'name'         => User::ENTITY_TYPE . '.sign_up',
                    'package_id'   => 'metafox/user',
                    'label_phrase' => 'user::activitypoint.action_type_sign_up_label',
                ],
                [
                    'name'         => User::ENTITY_TYPE . '.sign_in',
                    'package_id'   => 'metafox/user',
                    'label_phrase' => 'user::activitypoint.action_type_sign_in_label',
                ],
                [
                    'name'         => User::ENTITY_TYPE . '.new_profile_photo',
                    'package_id'   => 'metafox/user',
                    'label_phrase' => 'user::activitypoint.action_type_new_profile_photo_label',
                ],
                [
                    'name'         => User::ENTITY_TYPE . '.new_profile_cover',
                    'package_id'   => 'metafox/user',
                    'label_phrase' => 'user::activitypoint.action_type_new_profile_cover_label',
                ],
            ],
        ];
    }

    /**
     * @return string[]|null
     */
    public function getSiteStatContent(): ?array
    {
        return [
            User::ENTITY_TYPE => ['icon' => 'ico-user1-three'],
            'online_user'     => [
                'icon'      => 'ico-user1-check',
                'to'        => '/user/user/browse?status=online',
                'operation' => MetaFoxConstant::OPERATION_AGGREGATE_FUNCTION_MAX,
            ],
            'pending_user'    => [
                'icon' => 'ico-user1-clock',
                'to'   => '/user/user/browse?status=pending_approval',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return ['user'];
    }

    /**
     * @return array<int, mixed>
     */
    public function getAdMobPages(): array
    {
        return [
            [
                'path' => '/user',
                'name' => 'user::phrase.ad_mob_home_page',
            ],
            [
                'path' => '/user/:id',
                'name' => 'user::phrase.ad_mob_profile_page',
            ],
            [
                'path' => '/viewBlockedUser',
                'name' => 'user::phrase.ad_mob_blocked_page',
            ],
        ];
    }
}
