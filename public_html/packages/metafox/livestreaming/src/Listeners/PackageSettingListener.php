<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\LiveStreaming\Listeners;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Notifications\LiveVideoApproveNotification;
use MetaFox\LiveStreaming\Notifications\StartLiveStreamNotification;
use MetaFox\LiveStreaming\Policies\LiveVideoPolicy;
use MetaFox\LiveStreaming\Support\Handlers\EditPermissionListener;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getActivityTypes(): array
    {
        return [
            [
                'type'                         => LiveVideo::ENTITY_TYPE,
                'entity_type'                  => LiveVideo::ENTITY_TYPE,
                'is_active'                    => true,
                'title'                        => 'livestreaming::phrase.live_video_type',
                'description'                  => 'add_a_live_video',
                'is_system'                    => 0,
                'can_comment'                  => true,
                'can_like'                     => true,
                'can_share'                    => true,
                'can_edit'                     => false,
                'can_create_feed'              => true,
                'can_change_privacy_from_feed' => true,
                'can_redirect_to_detail'       => true,
                'params'                       => [
                    'isStreaming' => 'item.is_streaming',
                ],
            ],
        ];
    }

    public function getActivityForm(): array
    {
        return [];
    }

    public function getPolicies(): array
    {
        return [
            LiveVideo::class => LiveVideoPolicy::class,
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            LiveVideo::ENTITY_TYPE => [
                'limit_live_stream_time' => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 5,
                    ],
                    'extra' => [
                        'fieldCreator' => [EditPermissionListener::class, 'minLimitLiveTime'],
                    ],
                ],
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            LiveVideo::ENTITY_TYPE => [
                'view'     => UserRole::LEVEL_GUEST,
                'create'   => UserRole::LEVEL_PAGE,
                'update'   => UserRole::LEVEL_PAGE,
                'delete'   => UserRole::LEVEL_PAGE,
                'moderate' => UserRole::LEVEL_STAFF,
                'feature'  => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'purchase_feature' => [
                    'roles'     => UserRole::LEVEL_PAGE,
                    'is_public' => false,
                ],
                'approve'       => UserRole::LEVEL_STAFF,
                'save'          => UserRole::LEVEL_REGISTERED,
                'like'          => UserRole::LEVEL_REGISTERED,
                'share'         => UserRole::LEVEL_REGISTERED,
                'comment'       => UserRole::LEVEL_REGISTERED,
                'report'        => UserRole::LEVEL_REGISTERED,
                'auto_approved' => UserRole::LEVEL_REGISTERED,
                'sponsor'       => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'sponsor_free' => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
                'sponsor_in_feed' => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'auto_publish_sponsored_item' => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
            ],
        ];
    }

    public function getUserPrivacy(): array
    {
        return [
            'live_video.share_live_videos' => [
                'phrase' => 'livestreaming::phrase.user_privacy.who_can_share_live_videos',
            ],
            'live_video.view_browse_live_videos' => [
                'phrase' => 'livestreaming::phrase.user_privacy.who_can_view_browse_live_videos',
            ],
        ];
    }

    public function getUserPrivacyResource(): array
    {
        return [
            'page' => [
                'live_video.share_live_videos',
                'live_video.view_browse_live_videos',
            ],
            'group' => [
                'live_video.share_live_videos',
            ],
        ];
    }

    public function getDefaultPrivacy(): array
    {
        return [
            LiveVideo::ENTITY_TYPE => [
                'phrase'  => 'livestreaming::phrase.live_videos',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getProfileMenu(): array
    {
        return [
            LiveVideo::ENTITY_TYPE => [
                'phrase'  => 'livestreaming::phrase.live_videos',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'custom_video_playback_url'           => ['value' => ''],
            'custom_thumbnail_playback_url'       => ['value' => ''],
            'streaming_service'                   => ['value' => 'mux'],
            'filter_streaming_content_by_minutes' => ['value' => '10'],
            'reduce_latency'                      => ['value' => false],
            'display_live_section_on_mobile'      => ['value' => true],
            'live_video.purchase_sponsor_price'   => [
                'value'     => '',
                'is_public' => false,
            ],
            'allow_webcam_streaming' => ['value' => false],
            'webcam_websocket_url'   => ['value' => ''],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'start_livestream',
                'module_id'  => 'livestreaming',
                'title'      => 'livestreaming::phrase.friend_creates_a_new_live_video',
                'handler'    => StartLiveStreamNotification::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'livestreaming_approve_notification',
                'module_id'  => 'livestreaming',
                'handler'    => LiveVideoApproveNotification::class,
                'title'      => 'livestreaming::phrase.livestreaming_approve_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 2,
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'packages.installed' => [
                PackageInstalledListener::class,
            ],
            'models.notify.created' => [
                ModelCreatedListener::class,
            ],
            'models.notify.updated' => [
                ModelUpdatedListener::class,
            ],
            'models.notify.deleted' => [
                ModelDeletedListener::class,
            ],
            'like.notification_to_callback_message' => [
                LikeNotificationMessageListener::class,
            ],
            'comment.notification_to_callback_message' => [
                CommentNotificationMessageListener::class,
            ],
            'activity.update_feed_item_privacy' => [
                UpdateFeedItemPrivacyListener::class,
            ],
            'core.collect_total_items_stat' => [
                CollectTotalItemsStatListener::class,
            ],
            'models.notify.approved' => [
                ModelApprovedListener::class,
            ],
            'advertise.sponsor.enable_sponsor_feed' => [
                EnableSponsorFeedListener::class,
            ],
            'advertise.sponsor.disable_sponsor_feed' => [
                DisableSponsorFeedListener::class,
            ],
            'user.deleted' => [
                UserDeletedListener::class,
            ],
            'comment.allow_approved' => [
                CommentAllowApproved::class,
            ],
            'story.resource.get_extra_param' => [
                StoryGetExtraParamListener::class,
            ],
            'activity.feed.deleted' => [
                FeedDeletedListener::class,
            ],
        ];
    }

    public function getSiteStatContent(): ?array
    {
        return [
            LiveVideo::ENTITY_TYPE => ['icon' => 'ico-videocam'],
        ];
    }

    public function getSavedTypes(): array
    {
        return [
            [
                'label' => __p('livestreaming::phrase.live_videos'),
                'value' => 'live_video',
            ],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function getAdMobPages(): array
    {
        return [
            [
                'path' => '/livestreaming',
                'name' => 'livestreaming::phrase.ad_mob_home_page',
            ],
            [
                'path' => '/livestreaming/:id',
                'name' => 'livestreaming::phrase.ad_mob_detail_page',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return [
            LiveVideo::ENTITY_TYPE,
        ];
    }
}
