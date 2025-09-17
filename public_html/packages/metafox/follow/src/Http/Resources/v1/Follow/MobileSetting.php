<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Follow\Http\Resources\v1\Follow;

use MetaFox\Follow\Support\Browse\Scopes\ViewScope;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('follow')
            ->asGet()
            ->apiRules([
                'q'       => ['truthy', 'q'],
                'view'    => [
                    'includes', 'view', ViewScope::getAllowView(),
                ],
                'user_id' => ['truthy', 'user_id'],
            ]);

        $this->add('search')
            ->apiUrl('follow')
            ->apiParams([
                'q'       => ':q',
                'user_id' => ':id',
                'view'    => ':view',
            ]);
    }
}
