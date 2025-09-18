<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Socialite\Http\Resources\v1\Auth;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('login')
            ->apiUrl('socialite/login/:provider')
            ->asGet();

        $this->add('callback')
            ->apiUrl('socialite/callback/:provider')
            ->asGet()
            ->apiParams([
                'code' => ':code',
            ]);
    }
}
