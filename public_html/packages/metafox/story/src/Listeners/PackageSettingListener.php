<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Story\Listeners;

use Illuminate\Console\Scheduling\Schedule;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;
use MetaFox\Story\Jobs\ExpiredStoriesJob;
use MetaFox\Story\Jobs\UnmuteJob;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Notifications\NewStoryToFollowerNotification;
use MetaFox\Story\Notifications\RenewStoryNotification;
use MetaFox\Story\Notifications\StoryDoneProcessingNotification;
use MetaFox\Story\Notifications\StoryReactionNotification;
use MetaFox\Story\Policies\StoryPolicy;
use MetaFox\Story\Support\StorySupport;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getPolicies(): array
    {
        return [
            Story::class => StoryPolicy::class,
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Story::ENTITY_TYPE => [
                'view'     => UserRole::LEVEL_GUEST,
                'create'   => UserRole::LEVEL_PAGE,
                'delete'   => UserRole::LEVEL_PAGE,
                'comment'  => UserRole::LEVEL_REGISTERED,
                'like'     => UserRole::LEVEL_REGISTERED,
                'report'   => UserRole::LEVEL_REGISTERED,
                'moderate' => UserRole::LEVEL_STAFF,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'home_page_style'              => ['value' => StorySupport::DISPLAY_THE_USER_AVATAR],
            'video_service'                => ['value' => 'ffmpeg'],
            'only_friends'                 => ['value' => true],
            'duration_video_story'         => ['value' => StorySupport::STORY_VIDEO_DURATION_DEFAULT],
            'lifespan_options'             => ['value' => StorySupport::LIFESPAN_VALUE_OPTIONS],
            'lifespan_default'             => ['value' => StorySupport::USER_STORY_LIFESPAN],
            'data_item_map.story_reaction' => [
                'config_name' => 'notification.data_item_map.story_reaction',
                'module_id'   => 'notification',
                'is_public'   => 0,
                'value'       => 'like',
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'comment.notification_to_callback_message' => [
                CommentNotificationMessageListener::class,
            ],
            'user.deleted'                             => [
                UserDeletedListener::class,
            ],
            'user.attributes.extra'                    => [
                UserAttributesExtraListener::class,
            ],
            'user_story.attributes.extra'              => [
                UserAttributesExtraListener::class,
            ],
            'video.update_by_asset_id'                 => [
                DoneProcessVideoByAssetIdListener::class,
            ],
            'video.processing_failed'                  => [
                VideoProcessingFailed::class,
            ],
            'video.delete_by_asset_id'                 => [
                DeleteVideoByAssetIdListener::class,
            ],
            'livestreaming.build_integrate_field'      => [
                LiveStreamBuildIntegrateFieldListener::class,
            ],
            'livestreaming.create_story'               => [
                LiveStreamCreateStoryListener::class,
            ],
            'livestreaming.delete_live_video'          => [
                LiveStreamDeleteLiveListener::class,
            ],
            'livestreaming.updated_assets'             => [
                LiveStreamUpdatedAssetListener::class,
            ],
            'livestreaming.stop_live_video'            => [
                LiveStreamStopLiveListener::class,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'              => 'story_reaction',
                'module_id'         => 'story',
                'require_module_id' => 'like',
                'handler'           => StoryReactionNotification::class,
                'title'             => 'story::phrase.story_reaction_notification_type',
                'is_request'        => 0,
                'is_system'         => 1,
                'can_edit'          => 1,
                'channels'          => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'          => 17,
            ],
            [
                'type'       => 'suggest_create_story',
                'module_id'  => 'story',
                'handler'    => RenewStoryNotification::class,
                'title'      => 'story::phrase.suggest_create_story_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 18,
            ],
            [
                'type'       => 'story_video_done_processing',
                'module_id'  => 'story',
                'title'      => 'story::phrase.story_video_done_processing_type',
                'handler'    => StoryDoneProcessingNotification::class,
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'   => 19,
            ],
            [
                'type'              => 'story_follower_notification',
                'module_id'         => 'story',
                'require_module_id' => 'follow',
                'title'             => 'story::phrase.story_follower_notification_type',
                'handler'           => NewStoryToFollowerNotification::class,
                'is_request'        => 0,
                'is_system'         => 1,
                'can_edit'          => 1,
                'channels'          => ['database', 'mail', 'mobilepush', 'webpush'],
                'ordering'          => 19,
            ],
        ];
    }

    public function getUserPrivacy(): array
    {
        return [];
    }

    public function getUserValues(): array
    {
        return [];
    }

    public function registerApplicationSchedule(Schedule $schedule): void
    {
        $schedule->job(resolve(ExpiredStoriesJob::class))->everyFiveMinutes();
        $schedule->job(resolve(UnmuteJob::class))->everyFiveMinutes();
    }
}
