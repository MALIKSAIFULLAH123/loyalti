<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Poll\Http\Resources\v1\Poll\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

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
        $this->add('searchForm')
            ->apiUrl('admincp/core/form/poll.search');
    }
}
