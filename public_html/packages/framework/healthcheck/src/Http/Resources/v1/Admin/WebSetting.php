<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\HealthCheck\Http\Resources\v1\Admin;

use MetaFox\Form\Constants;
use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 * stub: /packages/resources/resource_admin_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewNotices')
            ->apiUrl('/admincp/health-check/notices');

        $this->add('refreshLicense')
            ->apiUrl('/admincp/health-check/license')
            ->apiMethod(Constants::METHOD_POST);
    }
}
