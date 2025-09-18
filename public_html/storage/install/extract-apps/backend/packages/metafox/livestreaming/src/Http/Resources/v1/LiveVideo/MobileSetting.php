<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('deleteItem')
            ->apiUrl('live-video/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('livestreaming::phrase.delete_confirm'),
                ]
            );

        $this->add('editItem')
            ->apiUrl('core/mobile/form/livestreaming.live_video.update/:id');

        $this->add('updateItemMobile')
            ->apiUrl('live-video/:id');

        $this->add('addItem')
            ->apiUrl('core/mobile/form/livestreaming.live_video.store')
            ->apiParams(['owner_id' => ':id']);

        $this->add('approveItem')
            ->apiUrl('live-video/approve/:id')
            ->asPatch();

        $this->add('sponsorItem')
            ->apiUrl('live-video/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('live-video/sponsor-in-feed/:id')
            ->asPatch();

        /*
         * @deprecated Remove in 5.2.0
         */
        $this->add('featureItem')
            ->apiUrl('live-video/feature/:id');

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('live-video/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('live-video/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('viewAll')
            ->apiUrl('live-video')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => ':view',
                'duration'    => ':duration',
                'streaming'   => ':streaming',
                'is_featured' => ':is_featured',
            ]);

        $this->add('viewAllStreaming')
            ->apiUrl('live-video')
            ->apiParams([
                'view' => 'all_streaming',
            ]);

        $this->add('viewMyStreaming')
            ->apiUrl('live-video')
            ->apiParams([
                'view' => 'my_streaming',
            ]);

        $this->add('viewOnOwner')
            ->apiUrl('live-video')
            ->apiParams([
                'user_id' => ':id',
            ]);

        $this->add('updateViewer')
            ->apiUrl('live-video/update-viewer/:id')
            ->asPut();

        $this->add('offNotification')
            ->apiUrl('live-video/off-notification/:id')
            ->asPut();

        $this->add('onNotification')
            ->apiUrl('live-video/on-notification/:id')
            ->asPut();

        $this->add('removeViewer')
            ->apiUrl('live-video/remove-viewer/:id')
            ->asPut();

        $this->add('pingStreaming')
            ->apiUrl('live-video/ping-streaming/:id');

        $this->add('pingViewer')
            ->apiUrl('live-video/ping-viewer/:id');

        $this->add('viewMyLiveVideos')
            ->apiUrl('live-video')
            ->apiParams(['view' => 'my']);

        $this->add('viewFriendLiveVideos')
            ->apiUrl('live-video')
            ->apiParams(['view' => 'friend']);

        $this->add('viewPendingLiveVideos')
            ->apiUrl('live-video')
            ->apiParams(['view' => 'pending']);

        $this->add('viewMyPendingLiveVideos')
            ->apiUrl('live-video')
            ->apiParams([
                'view' => 'my_pending',
            ]);

        $this->add('viewItem')
            ->apiUrl('live-video/:id')
            ->urlParams(['id' => ':id']);

        $this->add('searchItem')
            ->apiUrl('live-video')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => 'search',
                'duration'    => ':duration',
                'streaming'   => ':streaming',
                'is_featured' => ':is_featured',
            ])
            ->placeholder(__p('livestreaming::phrase.search_live_videos'));

        $this->add('searchGlobalLiveVideo')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'                        => 'live_video',
                'q'                           => ':q',
                'owner_id'                    => ':owner_id',
                'when'                        => ':when',
                'related_comment_friend_only' => ':related_comment_friend_only',
                'is_hashtag'                  => ':is_hashtag',
                'from'                        => ':from',
            ]);

        $this->add('searchInOwner')
            ->apiUrl('live-video')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('livestreaming::phrase.search_live_videos'));
    }
}
