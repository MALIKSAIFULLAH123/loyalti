<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Socialite\Http\Resources\v1;

use MetaFox\Socialite\Support\Facades\Provider;

/**
 | stub: src/Http/Resources/v1/PackageSetting.stub
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    /**
     * @return array<mixed>
     */
    public function getWebSettings(): array
    {
        return array_merge([], Provider::getProviderSettings());
    }

    /**
     * @return array<mixed>
     */
    public function getMobileSettings(): array
    {
        return array_merge([], Provider::getProviderSettings());
    }
}
