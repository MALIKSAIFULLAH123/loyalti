<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\EMoney\Http\Resources\v1\Statistic;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('getGatewaySettings')
            ->apiUrl('payment-gateway/configuration')
            ->asGet();

        $this->add('viewItem')
            ->apiUrl('emoney/statistic/:id');
    }
}
