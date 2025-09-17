<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Log\Listeners;

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

    private function injectLoggingConfig(array &$settings): void
    {
        // check for installation only.
        $data = app('files')->getRequire(base_path('config/logging.php'));
        // load original config
        $channels = $data['channels'] ?? [];

        $settings['default'] = [
            'config_name' => 'logging.default',
            'value'       => $data['default'] ?? 'public',
            'is_public'   => 0,
        ];

        // do not add to log
        //if (is_array($channels)) {
        //    foreach ($channels as $key => $values) {
        //        $name = sprintf('channels.%s', $key);
        //        $settings[$name] = [
        //            'config_name' => sprintf('logging.channels.%s', $key),
        //            'value'       => $values,
        //            'is_auto'     => 1,
        //            'is_public'   => 0,
        //        ];
        //    }
        //}
    }

    public function getSiteSettings(): array
    {
        $settings = [];

        $this->injectLoggingConfig($settings);

        return $settings;
    }
}
