<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Http\Resources\v1\Video;

use MetaFox\Platform\Resource\WebSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Video Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->pageUrl('video/search')
            ->pageParams([
                'view'        => Browse::VIEW_SEARCH,
                'category_id' => ':category_id',
            ])
            ->placeholder(__p('video::phrase.search_videos'));

        $this->add('viewAll')
            ->apiUrl('video')
            ->apiRules([
                'q'    => ['truthy', 'q'],
                'sort' => [
                    'includes', 'sort', [
                        Browse::SORT_RECENT,
                        Browse::SORT_MOST_LIKED,
                        Browse::SORT_MOST_VIEWED,
                        Browse::SORT_MOST_DISCUSSED,
                        Browse::SORT_A_TO_Z,
                        Browse::SORT_Z_TO_A,
                    ],
                ],
                'category_id' => ['truthy', 'category_id'],
                'is_featured' => ['truthy', 'is_featured'],
                'when'        => [
                    'includes', 'when', [
                        Browse::WHEN_ALL,
                        Browse::WHEN_THIS_MONTH,
                        Browse::WHEN_THIS_WEEK,
                        Browse::WHEN_TODAY,
                    ],
                ],
                'view' => [
                    'includes', 'view', [
                        Browse::VIEW_MY,
                        Browse::VIEW_FRIEND,
                        Browse::VIEW_PENDING,
                        Browse::VIEW_FEATURE,
                        Browse::VIEW_SPONSOR,
                        Browse::VIEW_SEARCH,
                        Browse::VIEW_MY_PENDING,
                    ],
                ],
            ]);

        $this->add('viewItem')
            ->apiUrl('video/:id')
            ->pageUrl('video/play/:id');

        $this->add('deleteItem')
            ->apiUrl('video/:id')
            ->pageUrl('video/all')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('video::phrase.delete_confirm'),
                ]
            );

        $this->add('addItem')
            ->pageUrl('video/share')
            ->apiUrl('core/form/video.store');

        $this->add('shareItem')
            ->pageUrl('video/share')
            ->apiUrl('core/form/video.share');

        $this->add('composerShareItem')
            ->apiUrl('core/form/video.composer.share');

        $this->add('editItem')
            ->pageUrl('video/edit/:id')
            ->apiUrl('core/form/video.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('video/edit/:id')
            ->apiUrl('core/form/video.update/:id');

        $this->add('approveItem')
            ->apiUrl('video/approve/:id')
            ->asPatch();

        $this->add('sponsorItem')
            ->apiUrl('video/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('video/sponsor-in-feed/:id')
            ->asPatch();

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('video/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('video/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('approveItem')
            ->apiUrl('video/approve/:id')
            ->asPatch();

        $this->add('fetchUrl')
            ->apiUrl('video/fetch')
            ->asPost();

        $this->add('increaseViewItem')
            ->apiUrl('video/increase-view/:id')
            ->asPatch();
    }
}
