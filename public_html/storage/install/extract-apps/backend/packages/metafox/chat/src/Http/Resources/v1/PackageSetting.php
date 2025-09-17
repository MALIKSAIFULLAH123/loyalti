<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Chat\Http\Resources\v1;

use MetaFox\Platform\Facades\Settings;

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
            'is_active' => !empty(Settings::get('broadcast.connections.pusher.key')),
        ];
    }

    public function getMobileSettings(): array
    {
        return [
            'is_active' => !empty(Settings::get('broadcast.connections.pusher.key')),
        ];
    }
}
