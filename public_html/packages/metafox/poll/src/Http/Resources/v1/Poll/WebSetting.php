<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Poll Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->pageUrl('poll/search')
            ->pageParams(['view' => Browse::VIEW_SEARCH])
            ->placeholder(__p('poll::phrase.search_polls'));

        $this->add('homePage')
            ->pageUrl('poll');

        $this->add('viewAll')
            ->apiUrl('poll')
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'sort'        => ['includes', 'sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed']],
                'when'        => ['includes', 'when', ['all', 'this_month', 'this_week', 'today']],
                'is_featured' => ['truthy', 'is_featured'],
                'view'        => [
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
            ->apiUrl('poll/:id')
            ->pageUrl('poll/:id');

        $this->add('deleteItem')
            ->apiUrl('poll/:id')
            ->pageUrl('poll/all')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('poll::phrase.delete_confirm'),
                ]
            );

        $this->add('addItem')
            ->pageUrl('poll/add')
            ->apiUrl('core/form/poll.store');

        $this->add('editItem')
            ->pageUrl('poll/edit/:id')
            ->apiUrl('core/form/poll.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('poll/edit/:id')
            ->apiUrl('core/form/poll.update/:id');

        $this->add('sponsorItem')
            ->apiUrl('poll/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('poll/sponsor-in-feed/:id')
            ->asPatch();

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('poll/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('poll/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('approveItem')
            ->apiUrl('poll/approve/:id')
            ->asPatch();
    }
}
