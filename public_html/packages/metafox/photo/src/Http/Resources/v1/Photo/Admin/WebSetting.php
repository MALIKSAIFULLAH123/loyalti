<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Http\Resources\v1\Photo\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_admin_setting.stub
 * Add this class name to resources config gateway
 */
class WebSetting extends ResourceSetting
{

    protected function initialize(): void
    {
        $this->add('searchForm')
            ->apiUrl('admincp/core/form/photo.search');
    }
}
