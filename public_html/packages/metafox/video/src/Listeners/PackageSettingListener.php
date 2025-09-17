<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Jobs\CheckVideoExistenceJob;
use MetaFox\Video\Models\Category;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Notifications\NewVideoToFollowerNotification;
use MetaFox\Video\Notifications\VideoApproveNotification;
use MetaFox\Video\Notifications\VideoDoneProcessingNotification;
use MetaFox\Video\Notifications\VideoProcessedFailedNotification;
use MetaFox\Video\Policies\CategoryPolicy;
use MetaFox\Video\Policies\VideoPolicy;

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getActivityTypes(): array
    {
        return [
            [
                'type'                         => Video::ENTITY_TYPE,
                'entity_type'                  => Video::ENTITY_TYPE,
                'is_active'                    => true,
                'title'                        => 'video::phrase.video_type',
                'description'                  => 'added_a_video',
                'is_system'                    => 0,
                'can_comment'                  => true,
                'can_like'                     => true,
                'can_share'                    => true,
                'can_edit'                     => true,
                'can_create_feed'              => true,
                'can_change_privacy_from_feed' => true,
            ],
        ];
    }

    public function getActivityForm(): array
    {
        return [
            Video::ENTITY_TYPE => [
                // setting more here.
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Video::class    => VideoPolicy::class,
            Category::class => CategoryPolicy::class,
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            Video::ENTITY_TYPE => [
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
                'flood_control'                                       => [
                    'description' => 'video_flood_control_description',
                    'type'        => MetaFoxDataType::INTEGER,
                    'default'     => 0,
                    'roles'       => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'mature_video_age_limit'                              => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 18,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 18,
                        UserRole::STAFF_USER  => 18,
                        UserRole::NORMAL_USER => 18,
                    ],
                ],
            ],
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Video::ENTITY_TYPE => [
                'view'                        => UserRole::LEVEL_GUEST,
                'create'                      => UserRole::LEVEL_PAGE,
                'update'                      => UserRole::LEVEL_PAGE,
                'delete'                      => UserRole::LEVEL_PAGE,
                'moderate'                    => UserRole::LEVEL_STAFF,
                'feature'                     => [
                    'roles'     => UserRole::LEVEL_PAGE,
                    'is_public' => false,
                ],
                'purchase_feature'            => [
                    'roles'     => UserRole::LEVEL_PAGE,
                    'is_public' => false,
                ],
                'approve'                     => UserRole::LEVEL_STAFF,
                'save'                        => UserRole::LEVEL_REGISTERED,
                'like'                        => UserRole::LEVEL_REGISTERED,
                'share'                       => UserRole::LEVEL_REGISTERED,
                'comment'                     => UserRole::LEVEL_REGISTERED,
                'report'                      => UserRole::LEVEL_REGISTERED,
                'auto_approved'               => UserRole::LEVEL_PAGE,
                'upload_video_file'           => UserRole::LEVEL_REGISTERED,
                'share_video_url'             => UserRole::LEVEL_REGISTERED,
                'add_mature_video'            => UserRole::LEVEL_REGISTERED,
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
            ],
        ];
    }

    public function getUserPrivacy(): array
    {
        return [
            'video.share_videos'       => [
                'phrase'  => 'video::phrase.user_privacy.who_can_share_videos',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
            'video.view_browse_videos' => [
                'phrase'  => 'video::phrase.user_privacy.who_can_view_videos',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getUserPrivacyResource(): array
    {
        return [
            'page'  => [
                'video.share_videos',
                'video.view_browse_videos',
            ],
            'group' => [
                'video.share_videos',
            ],
        ];
    }

    public function getDefaultPrivacy(): array
    {
        return [
            Video::ENTITY_TYPE => [
                'phrase'  => 'video::phrase.videos',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getProfileMenu(): array
    {
        return [
            Video::ENTITY_TYPE => [
                'phrase'  => 'video::phrase.videos',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'video_service'                => ['value' => 'ffmpeg'],
            'minimum_name_length'          => ['value' => MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH],
            'maximum_name_length'          => ['value' => 100],
            'default_category'             => ['value' => 1],
            'video.purchase_sponsor_price' => [
                'value'     => '',
                'is_public' => false,
            ],
            'minimal_play_time_threshold'  => [
                'value' => 3, // default value in second
            ],
            'enable_video_uploads'         => [
                'value' => true,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'video_done_processing',
                'module_id'  => 'video',
                'title'      => 'video::phrase.video_done_processing_type',
                'handler'    => VideoDoneProcessingNotification::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 1,
            ],
            [
                'type'       => 'video_processed_failed',
                'module_id'  => 'video',
                'title'      => 'video::phrase.video_processed_failed_type',
                'handler'    => VideoProcessedFailedNotification::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 3,
            ],
            [
                'type'       => 'video_approve_notification',
                'module_id'  => 'video',
                'handler'    => VideoApproveNotification::class,
                'title'      => 'video::phrase.video_approved_notification',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 2,
            ],
            [
                'type'              => 'video_follower_notification',
                'module_id'         => 'video',
                'require_module_id' => 'follow',
                'handler'           => NewVideoToFollowerNotification::class,
                'title'             => 'video::phrase.video_follower_notification_type',
                'is_request'        => 0,
                'is_system'         => 1,
                'can_edit'          => 1,
                'channels'          => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'          => 2,
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'photo.media_upload'                       => [
                MediaUploadListener::class,
            ],
            'photo.media_update'                       => [
                MediaUpdateListener::class,
            ],
            'photo.media_remove'                       => [
                MediaRemoveListener::class,
            ],
            'photo.media_add_to_album'                 => [
                MediaAddToAlbumListener::class,
            ],
            'feed.composer.edit'                       => [
                FeedComposerEditListener::class,
            ],
            'activity.update_feed_item_privacy'        => [
                UpdateFeedItemPrivacyListener::class,
            ],
            'photo.media_patch_update'                 => [
                MediaPatchUpdateListener::class,
            ],
            'like.notification_to_callback_message'    => [
                LikeNotificationMessageListener::class,
            ],
            'feed.get_url_item_by_id'                  => [
                GetUrlVideoByIdListener::class,
            ],
            'feed.pre_composer_create'                 => [
                PreComposerCreateListener::class,
            ],
            'feed.pre_composer_edit'                   => [
                PreComposerEditListener::class,
            ],
            'video.pre_video_create'                   => [
                PreVideoCreateListener::class,
            ],
            'photo.pre_photo_upload_media'             => [
                PrePhotoUploadMediaListener::class,
            ],
            'photo.album.pre_photo_album_upload_media' => [
                PrePhotoAlbumUploadMediaListener::class,
            ],
            'photo.album.pre_photo_album_create'       => [
                PrePhotoAlbumCreateListener::class,
            ],
            'photo.album.pre_photo_album_update'       => [
                PrePhotoAlbumUpdateListener::class,
            ],
            'photo.album.can_upload_to_album'          => [
                CanUploadToAlbumListener::class,
            ],
            'photo.upload_with_photo'                  => [
                CanUploadWithPhotoListener::class,
            ],
            'core.collect_total_items_stat'            => [
                CollectTotalItemsStatListener::class,
            ],
            'comment.notification_to_callback_message' => [
                CommentNotificationMessageListener::class,
            ],
            'packages.installed'                       => [
                PackageInstalledListener::class,
            ],
            'user.deleted'                             => [
                UserDeletedListener::class,
            ],
            'video.update_by_asset_id'                 => [
                DoneProcessVideoByAssetIdListener::class,
            ],
            'video.delete_by_asset_id'                 => [
                DeleteVideoByAssetIdListener::class,
            ],
            'video.upload_thumbnail_by_asset_id'       => [
                UploadVideoThumbnailByAssetIdListener::class,
            ],
            'advertise.sponsor.enable_sponsor_feed'    => [
                EnableSponsorFeedListener::class,
            ],
            'advertise.sponsor.disable_sponsor_feed'   => [
                DisableSponsorFeedListener::class,
            ],
            'video.check_ready_service'                => [
                CheckReadyService::class,
            ],
            'video.processing_failed'                  => [
                VideoProcessingFailed::class,
            ],
            'importer.completed'                       => [
                ImporterCompleted::class,
            ],
            'activity.feed.deleted'                    => [
                FeedDeletedListener::class,
            ],
            'photo.query_album_items'                  => [
                QueryAlbumItemsListener::class,
            ],
        ];
    }

    public function getSiteStatContent(): ?array
    {
        return [
            Video::ENTITY_TYPE => ['icon' => 'ico-video-player'],
            'pending_video'    => [
                'icon' => 'ico-clock-o',
                'to'   => '/video/video/browse?view=' . Browse::VIEW_PENDING,
            ],
        ];
    }

    public function getSavedTypes(): array
    {
        return [
            [
                'label' => __p('video::phrase.videos'),
                'value' => 'video',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return ['video', 'video_category'];
    }

    /**
     * @return array<int, mixed>
     */
    public function getAdMobPages(): array
    {
        return [
            [
                'path' => '/video',
                'name' => 'video::phrase.ad_mob_video_home_page',
            ],
        ];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(CheckVideoExistenceJob::class)
            ->everyFiveMinutes()
            ->withoutOverlapping();
    }
}
