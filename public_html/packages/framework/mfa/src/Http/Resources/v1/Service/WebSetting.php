<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Http\Resources\v1\Service;

use MetaFox\Form\Constants;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiMethod(Constants::METHOD_GET)
            ->apiUrl('mfa/service')
            ->apiParams([
                'resolution' => MetaFoxConstant::RESOLUTION_WEB,
            ]);
    }
}
