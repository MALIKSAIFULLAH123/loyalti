<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\IntegratedModule;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('menuSetting')
            ->asGet()
            ->apiUrl('core/form/group.integrated_module/:id');

        $this->add('orderingItem')
            ->asPost()
            ->apiUrl('group-integrated/order')
            ->apiParams([
                'group_id' => ':id',
                'names'    => ':names',
            ]);
    }
}
