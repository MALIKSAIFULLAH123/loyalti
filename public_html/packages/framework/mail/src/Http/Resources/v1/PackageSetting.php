<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mail\Http\Resources\v1;

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
            'mail_service_is_ready' => app('mail')->validateConfiguration(),
        ];
    }

    public function getMobileSettings(): array
    {
        return [
            'mail_service_is_ready' => app('mail')->validateConfiguration(),
        ];
    }
}
