<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\TourGuide\Http\Resources\v1\Step;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('addItem')
            ->apiParams([
                'tour_guide_id' => ':tour_guide_id',
                'page_name'     => ':page_name',
            ])
            ->apiUrl('core/form/tourguide.tour_guide_step.store');
    }
}
