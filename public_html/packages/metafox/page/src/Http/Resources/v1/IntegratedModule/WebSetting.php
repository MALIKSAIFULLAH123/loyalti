<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Http\Resources\v1\IntegratedModule;

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
            ->apiUrl('core/form/page.integrated_module/:id');

        $this->add('orderingItem')
            ->asPost()
            ->apiUrl('page-integrated/order')
            ->apiParams([
                'page_id' => ':id',
                'names'   => ':names',
            ]);
    }
}
