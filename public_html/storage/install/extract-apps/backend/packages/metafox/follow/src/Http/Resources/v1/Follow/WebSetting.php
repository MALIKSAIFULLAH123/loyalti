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
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewFollow')
            ->apiUrl('follow')
            ->asGet()
            ->apiRules([
                'q'    => ['truthy', 'q'],
                'view' => [
                    'includes', 'view', ViewScope::getAllowView(),
                ],
            ]);

        $this->add('viewProfile')
            ->apiUrl('follow')
            ->asGet()
            ->apiParams([
                'user_id' => ':id',
                'view'    => ViewScope::VIEW_FOLLOWING,
                'limit'   => 6,
            ]);

        $this->add('viewFollower')
            ->apiUrl('follow')
            ->asGet()
            ->apiParams([
                'user_id' => ':id',
                'view'    => ViewScope::VIEW_FOLLOWER,
                'limit'   => 6,
            ]);
    }
}
