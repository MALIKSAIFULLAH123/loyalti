<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Featured\Http\Resources\v1\Item\Admin;

use MetaFox\Platform\MetaFoxForm;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_admin_setting.stub
 * Add this class name to resources config gateway
 */

class WebSetting extends ResourceSetting
{

    protected function initialize():void
    {
        $this->add('searchSettingForm')
            ->apiRules([
                'role_id' => ['truthy', 'role_id'],
            ])
            ->apiParams([
                'role_id' => ':role_id',
            ])
            ->apiUrl('admincp/core/form/featured.item.search_setting_form');

        $this->add('settingForm')
            ->apiRules([
                'role_id' => ['truthy', 'role_id'],
            ])
            ->apiParams([
                'role_id' => ':role_id',
            ])
            ->apiUrl('admincp/setting/form/featured/item');
    }
}
