<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Broadcast\Http\Resources\v1;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;

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
    public function getMobileSettings(): array
    {
        return $this->getDefaultSettingForMobileApi();
    }

    /**
     * @return array
     * @deprecated MetaFox v5.2
     */
    private function getDefaultSettingForMobileApi(): array
    {
        if (!MetaFox::isMobile()) {
            return [];
        }

        if (version_compare(MetaFox::getApiVersion(), 'v1.5', '>=')) {
            return [];
        }

        return [
            'connections.pusher' => Settings::get('broadcast.connections.pusher')
        ];
    }
}
