<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Sms\Http\Resources\v1;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getWebSettings(): array
    {
        return [
            'sms_service_is_ready' => app('sms')->validateConfiguration(),
        ];
    }

    public function getMobileSettings(): array
    {
        return [
            'sms_service_is_ready' => app('sms')->validateConfiguration(),
        ];
    }
}
