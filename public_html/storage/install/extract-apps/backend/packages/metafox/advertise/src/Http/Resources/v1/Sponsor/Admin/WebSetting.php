<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_admin_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('editSettingForm')
            ->apiRules([
                'role_id' => ['truthy', 'role_id'],
            ])
            ->apiParams([
                'role_id' => ':role_id',
            ])
            ->apiUrl('admincp/core/form/advertise.advertise_sponsor.setting_form');

        $this->add('editSearchSettingForm')
            ->apiRules([
                'role_id' => ['truthy', 'role_id'],
            ])
            ->apiParams([
                'role_id' => ':role_id',
            ])
            ->apiUrl('admincp/core/form/advertise.advertise_sponsor.search_setting_form');

        $this->add('searchForm')
            ->apiUrl('admincp/core/form/advertise.advertise_sponsor.search_form');
    }
}
