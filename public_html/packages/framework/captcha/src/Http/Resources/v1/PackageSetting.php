<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Captcha\Http\Resources\v1;

use MetaFox\Captcha\Support\Facades\Captcha;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getWebSettings(): array
    {
        return [
            'rules' => Captcha::getRules(),
        ];
    }

    public function getMobileSettings(): array
    {
        return [];
    }
}
