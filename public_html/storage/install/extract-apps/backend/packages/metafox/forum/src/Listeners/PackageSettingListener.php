<?php

namespace MetaFox\Forum\Listeners;

use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Notifications\AddModerator;
use MetaFox\Forum\Notifications\AdminUpdateThread;
use MetaFox\Forum\Notifications\ApprovedPost;
use MetaFox\Forum\Notifications\ApprovedThread;
use MetaFox\Forum\Notifications\CopyThread;
use MetaFox\Forum\Notifications\CreatePost;
use MetaFox\Forum\Notifications\DisplayWiki;
use MetaFox\Forum\Notifications\MergeThread;
use MetaFox\Forum\Notifications\NewThreadToFollowerNotification;
use MetaFox\Forum\Notifications\SubscribedThread;
use MetaFox\Forum\Policies\ForumPolicy;
use MetaFox\Forum\Policies\ForumPostPolicy;
use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;

/**
 * class PackageSettingListener.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getActivityTypes(): array
    {
        return [
            [
                'type'                   => ForumThread::ENTITY_TYPE,
                'entity_type'            => ForumThread::ENTITY_TYPE,
                'is_active'              => true,
                'title'                  => 'forum::phrase.forum_thread_type',
                'description'            => 'added_a_thread',
                'is_system'              => 0,
                'can_comment'            => true,
                'can_like'               => true,
                'can_share'              => true,
                'can_edit'               => false,
                'can_create_feed'        => true,
                'can_redirect_to_detail' => true,
                'action_on_feed'         => true,
            ],
            [
                'type'                   => ForumPost::ENTITY_TYPE,
                'entity_type'            => ForumPost::ENTITY_TYPE,
                'is_active'              => true,
                'title'                  => 'forum::phrase.forum_post_type',
                'description'            => 'added_a_post',
                'is_system'              => 0,
                'can_comment'            => false,
                'can_like'               => false,
                'can_share'              => false,
                'can_edit'               => false,
                'can_create_feed'        => true,
                'can_redirect_to_detail' => false,
                'action_on_feed'         => false,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        $ordering = 0;

        return [
            [
                'type'       => 'approved_post',
                'module_id'  => 'forum',
                'title'      => 'forum::phrase.post_successfully_approved',
                'handler'    => ApprovedPost::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'approved_thread',
                'module_id'  => 'forum',
                'title'      => 'forum::phrase.thread_successfully_approved',
                'handler'    => ApprovedThread::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'copy_thread',
                'module_id'  => 'forum',
                'title'      => 'forum::phrase.thread_successfully_copied',
                'handler'    => CopyThread::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'subscribed_thread',
                'module_id'  => 'forum',
                'title'      => 'forum::phrase.subscribed_thread_notification',
                'handler'    => SubscribedThread::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'admin_update_thread',
                'module_id'  => 'forum',
                'title'      => 'forum::phrase.update_thread_notification',
                'handler'    => AdminUpdateThread::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'create_post',
                'module_id'  => 'forum',
                'title'      => 'forum::phrase.create_post_notification',
                'handler'    => CreatePost::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'merge_thread',
                'module_id'  => 'forum',
                'title'      => 'forum::phrase.merge_thread_notification',
                'handler'    => MergeThread::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'display_wiki',
                'module_id'  => 'forum',
                'title'      => 'forum::phrase.display_wiki_notification',
                'handler'    => DisplayWiki::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'       => 'add_moderator',
                'module_id'  => 'forum',
                'title'      => 'forum::phrase.add_moderator_notification',
                'handler'    => AddModerator::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => ++$ordering,
            ],
            [
                'type'              => 'forum_thread_follower_notification',
                'module_id'         => 'forum',
                'require_module_id' => 'follow',
                'handler'           => NewThreadToFollowerNotification::class,
                'title'             => 'forum::phrase.forum_thread_follower_notification_type',
                'is_request'        => 0,
                'is_system'         => 1,
                'can_edit'          => 1,
                'channels'          => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'          => 20,
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Forum::ENTITY_TYPE       => [
                'view' => UserRole::LEVEL_GUEST,
            ],
            ForumThread::ENTITY_TYPE => [
                'create'                      => UserRole::LEVEL_REGISTERED,
                'moderate'                    => UserRole::LEVEL_STAFF,
                'update_own'                  => UserRole::LEVEL_REGISTERED,
                'delete_own'                  => UserRole::LEVEL_REGISTERED,
                'subscribe'                   => UserRole::LEVEL_REGISTERED,
                'stick'                       => UserRole::LEVEL_STAFF,
                'close_own'                   => UserRole::LEVEL_REGISTERED,
                'save'                        => UserRole::LEVEL_REGISTERED,
                'copy'                        => UserRole::LEVEL_REGISTERED,
                'merge_own'                   => UserRole::LEVEL_REGISTERED,
                'approve'                     => UserRole::LEVEL_STAFF,
                'attach_poll'                 => UserRole::LEVEL_REGISTERED,
                'auto_approved'               => UserRole::LEVEL_REGISTERED,
                'like'                        => UserRole::LEVEL_REGISTERED,
                'share'                       => UserRole::LEVEL_REGISTERED,
                'report'                      => UserRole::LEVEL_REGISTERED,
                'create_as_wiki'              => UserRole::LEVEL_ADMINISTRATOR,
                'sponsor'                     => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'sponsor_free'                => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
                'sponsor_in_feed'             => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'auto_publish_sponsored_item' => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
                'move'                        => UserRole::LEVEL_REGISTERED,
                'download_attachment'         => UserRole::LEVEL_REGISTERED,
            ],
            ForumPost::ENTITY_TYPE   => [
                'reply'               => UserRole::LEVEL_REGISTERED,
                'reply_own'           => UserRole::LEVEL_REGISTERED,
                'update_own'          => UserRole::LEVEL_REGISTERED,
                'delete_own'          => UserRole::LEVEL_REGISTERED,
                'auto_approved'       => UserRole::LEVEL_REGISTERED,
                'approve'             => UserRole::LEVEL_STAFF,
                'quote'               => UserRole::LEVEL_REGISTERED,
                'like'                => UserRole::LEVEL_REGISTERED,
                'share'               => UserRole::LEVEL_REGISTERED,
                'report'              => UserRole::LEVEL_REGISTERED,
                'save'                => UserRole::LEVEL_REGISTERED,
                'download_attachment' => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            ForumThread::ENTITY_TYPE => [
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
            ForumPost::ENTITY_TYPE   => [
                'flood_control' => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'minimum_name_length'                 => ['value' => 3],
            'maximum_name_length'                 => ['value' => 155],
            'forum_thread.purchase_sponsor_price' => [
                'value'     => '',
                'is_public' => false,
            ],
        ];
    }

    public function getUserPrivacy(): array
    {
        return [
            'forum.share_forum_thread' => [
                'phrase' => 'forum::phrase.user_privacy.who_can_share_forum_thread',
            ],
            'forum.reply_forum_thread' => [
                'phrase' => 'forum::phrase.user_privacy.who_can_reply_forum_thread',
            ],
        ];
    }

    public function getUserPrivacyResource(): array
    {
        return [
            'page' => [
                'forum.share_forum_thread',
                'forum.reply_forum_thread',
            ],
        ];
    }

    public function getDefaultPrivacy(): array
    {
        return [];
    }

    public function getProfileMenu(): array
    {
        return [
            Forum::ENTITY_TYPE => [
                'phrase'  => 'forum::phrase.forums',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Forum::class       => ForumPolicy::class,
            ForumThread::class => ForumThreadPolicy::class,
            ForumPost::class   => ForumPostPolicy::class,
        ];
    }

    /**
     * @return string[][]
     */
    public function getEvents(): array
    {
        return [
            'models.notify.approved'                 => [
                ModelApprovedListener::class,
            ],
            'like.notification_to_callback_message'  => [
                LikeNotificationListener::class,
            ],
            'core.collect_total_items_stat'          => [
                CollectTotalItemsStatListener::class,
            ],
            'user.like_notification'                 => [
                LikeOwnerNotificationListener::class,
            ],
            'user.deleted'                           => [
                UserDeletedListener::class,
            ],
            'poll.integration.check_permission'      => [
                CheckIntegrationPermissionListener::class,
            ],
            'poll.integration.get_parent'            => [
                GetIntegrationParentListener::class,
            ],
            'poll.update_thread_integration'         => [
                UpdateThreadIntegrationListener::class,
            ],
            'importer.completed'                     => [
                ImporterCompleted::class,
            ],
            'advertise.sponsor.enable_sponsor_feed'  => [
                EnableSponsorFeedListener::class,
            ],
            'advertise.sponsor.disable_sponsor_feed' => [
                DisableSponsorFeedListener::class,
            ],
            'activity.feed.deleted'                  => [
                FeedDeletedListener::class,
            ],
            'models.notify.deleted'                  => [
                ModelDeletedListener::class,
            ],
        ];
    }

    public function getCaptchaRules(): array
    {
        return [ForumSupport::CAPTCHA_RULE_CREATE_THREAD, ForumSupport::CAPTCHA_RULE_CREATE_POST];
    }

    public function getSiteStatContent(): ?array
    {
        return [
            ForumPost::ENTITY_TYPE   => ['icon' => 'ico-comments-o'],
            ForumThread::ENTITY_TYPE => ['icon' => 'ico-comments-square'],
            'pending_forum_post'     => ['icon' => 'ico-sandclock-start-o', 'to' => '/forum/forum-post/browse?view=' . Browse::VIEW_PENDING],
            'pending_forum_thread'   => ['icon' => 'ico-sandclock-start-o', 'to' => '/forum/forum-thread/browse?view=' . Browse::VIEW_PENDING],
        ];
    }

    public function getSavedTypes(): array
    {
        return [
            [
                'label' => __p('forum::phrase.forum_thread_stat_label'),
                'value' => 'forum_thread',
            ],
            [
                'label' => __p('forum::phrase.forum_post_stat_label'),
                'value' => 'forum_post',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return ['forum', 'forum_thread', 'forum_post'];
    }

    /**
     * @return array<int, mixed>
     */
    public function getAdMobPages(): array
    {
        return [
            [
                'path' => '/forum/forum_thread',
                'name' => 'forum::phrase.ad_mob_thread_home_page',
            ],
            [
                'path' => '/forum/forum_thread/:id',
                'name' => 'forum::phrase.ad_mob_thread_detail_page',
            ],
            [
                'path' => '/forum/forum_post',
                'name' => 'forum::phrase.ad_mob_post_home_page',
            ],
        ];
    }
}
