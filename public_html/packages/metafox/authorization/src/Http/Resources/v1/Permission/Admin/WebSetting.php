<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Authorization\Http\Resources\v1\Permission\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 *--------------------------------------------------------------------------
 * User Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting
 * Inject this class into property $resources.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchForm')
            ->apiUrl('admincp/core/form/user_permission.search')
            ->apiParams([
                'module_id' => ':module_id',
                'role_id' => ':role_id',
                'app' => ':appName',
            ]);
        $this->add('editForm')
            ->apiUrl('admincp/authorization/permission/form')
            ->apiParams([
                'module_id' => ':module_id',
                'role_id' => ':role_id',
                'app' => ':appName',
            ]);
    }
}
