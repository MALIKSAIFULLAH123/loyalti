<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Advertise\Http\Resources\v1;

use MetaFox\Advertise\Support\Facades\Support;

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
        $context = user();

        return [
            'placements' => Support::getActivePlacementsForSetting(),
        ];
    }

    public function getMobileSettings(): array
    {
        return [];
    }
}
