<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\ExampleRule\Admin;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * GroupRuleExample Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('admincp/group/example-rule');

        $this->add('deleteItem')
            ->apiUrl('admincp/group/example-rule/:id');

        $this->add('addItem')
            ->apiUrl('admincp/group/example-rule/form');

        $this->add('editItem')
            ->apiUrl('admincp/group/example-rule/form/:id');

        $this->add('activeItem')
            ->apiUrl('admincp/group/example-rule/active/:id');
    }
}
