<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\FFMPEG\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getSiteSettings(): array
    {
        return [
            'binaries'         => ['value' => '/usr/bin/ffmpeg', 'is_public' => false],
            'ffprobe_binaries' => ['value' => '/usr/bin/ffprobe', 'is_public' => false],
            'timeout'          => [
                'env_var'   => 'MFOX_FFMPEG_TIMEOUT',
                'value'     => 900,
                'is_public' => false,
            ],
            'threads' => [
                'env_var'   => 'MFOX_FFMPEG_THREADS',
                'value'     => 8,
                'is_public' => false,
            ],
        ];
    }
}
