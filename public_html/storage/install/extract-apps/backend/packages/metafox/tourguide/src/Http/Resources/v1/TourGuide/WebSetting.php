<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\TourGuide\Http\Resources\v1\TourGuide;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('getActions')
            ->apiParams(['page_name' => ':page_name'])
            ->apiUrl('tour-guide/actions');

        $this->add('viewItem')
            ->apiUrl('tour-guide/:id');

        $this->add('addItem')
            ->pageUrl('tour-guide/add')
            ->apiUrl('core/form/tourguide.tour_guide.store');

        $this->add('markAsActive')
            ->apiUrl('tour-guide/:id/active')
            ->asPatch();

        $this->add('hideItem')
            ->asPost()
            ->apiParams(['tour_guide_id' => ':id'])
            ->apiUrl('tour-guide/hidden')
            ->confirm([
                'title'   => __p('tourguide::phrase.tour_guide'),
                'message' => __p('tourguide::phrase.do_not_show_it_again_for_this_page'),
            ]);

        $this->add('unhideItem')
            ->asDelete()
            ->apiParams(['tour_guide_id' => ':id'])
            ->apiUrl('tour-guide/hidden');
    }
}
