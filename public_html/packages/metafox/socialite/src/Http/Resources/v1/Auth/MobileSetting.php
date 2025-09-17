<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Socialite\Http\Resources\v1\Auth;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('callback')
            ->apiUrl('socialite/callback/:provider')
            ->asGet()
            ->apiParams([
                'code'          => ':code',
                'token'         => ':token',
                'code_verifier' => ':code_verifier',
            ]);
    }
}
