<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest;

use MetaFox\ActivityPoint\Support\Facade\PointConversion;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('activitypoint/conversion-request');

        $this->add('addItem')
            ->apiUrl('core/form/activitypoint.activitypoint_conversion_request.store');

        $this->add('cancelItem')
            ->apiUrl('activitypoint/conversion-request/:id/cancel')
            ->asPatch()
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('activitypoint::phrase.are_you_sure_you_want_to_cancel_this_request'),
            ]);

        $this->add('viewAll')
            ->apiUrl('activitypoint/conversion-request')
            ->apiParams([
                'from_date' => ':from_date',
                'to_date'   => ':to_date',
                'status'    => ':status',
                'id'        => ':id',
            ])
            ->apiRules([
                'from_date' => ['truthy', 'from_date'],
                'to_date'   => ['truthy', 'to_date'],
                'status'    => ['includes', 'status', array_column(PointConversion::getConversionRequestStatusOptions(), 'value')],
                'id'        => ['truthy', 'id'],
            ]);

        $this->add('getGrid')
            ->apiUrl('core/grid/activitypoint.conversion_request');
    }
}
