<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\ViewScope;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('deleteItem')
            ->apiUrl('live-video/:id')
            ->pageUrl('live-video/all')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('livestreaming::phrase.delete_confirm'),
                ]
            );
        $this->add('editItem')
            ->pageUrl('live-video/edit/:id')
            ->apiUrl('core/form/live_video.update/:id');

        $this->add('editLivestream')
            ->pageUrl('live-video/dashboard/:id')
            ->apiUrl('core/form/live_video.edit_livestream/:id');

        $this->add('editFeedItem')
            ->pageUrl('live-video/edit/:id')
            ->apiUrl('core/form/live_video.update/:id');

        $this->add('addItem')
            ->pageUrl('live-video/add')
            ->apiUrl('core/form/live_video.store');

        $this->add('addWebcamItem')
            ->pageUrl('live-video/add-webcam')
            ->apiUrl('core/form/live_video.store_webcam');

        $this->add('approveItem')
            ->apiUrl('live-video/approve/:id')
            ->asPatch();

        $this->add('sponsorItem')
            ->apiUrl('live-video/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('live-video/sponsor-in-feed/:id')
            ->asPatch();

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

        $this->add('searchItem')
            ->pageUrl('live-video/search')
            ->placeholder(__p('livestreaming::phrase.search_live_videos'));

        $this->add('homePage')
            ->pageUrl('live-video');

        $this->add('viewAll')
            ->pageUrl('live-video/all')
            ->apiUrl('live-video')
            ->apiRules([
                'q' => [
                    'truthy', 'q',
                ], 'sort' => ['includes', 'sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed']],
                'tag'  => ['truthy', 'tag'],
                'when' => ['includes', 'when', ['all', 'this_month', 'this_week', 'today']],
                'view' => [
                    'includes', 'view', ViewScope::getAllowView(),
                ],
                'duration'    => ['includes', 'duration', ['longer', 'shorter']],
                'streaming'   => ['truthy', 'streaming'],
                'is_featured' => ['truthy', 'is_featured'],
            ]);

        $this->add('viewItem')
            ->pageUrl('live-video/:id')
            ->apiUrl('live-video/:id');

        $this->add('updateViewer')
            ->apiUrl('live-video/update-viewer/:id')
            ->asPut();

        $this->add('pingStreaming')
            ->apiUrl('live-video/ping-streaming/:id')
            ->asGet();

        $this->add('pingViewer')
            ->apiUrl('live-video/ping-viewer/:id');

        $this->add('removeViewer')
            ->apiUrl('live-video/remove-viewer/:id')
            ->asPut();

        $this->add('startGoLive')
            ->apiUrl('live-video/go-live')
            ->asPost();

        $this->add('endLive')
            ->apiUrl('live-video/end-live/:id')
            ->asPost()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('livestreaming::phrase.are_you_sure_you_want_to_end_this_live_video'),
                ]
            );

        $this->add('offNotification')
            ->apiUrl('live-video/off-notification/:id')
            ->asPut();

        $this->add('onNotification')
            ->apiUrl('live-video/on-notification/:id')
            ->asPut();
    }
}
