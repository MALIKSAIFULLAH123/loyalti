<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Queue\Listeners;

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
        $settings = [
            'retry_timeout' => ['value' => 90],
        ];

        // check for installation only.
        $data = app('files')->getRequire(base_path('config/queue.php'));
        // load original config
        $connections = $data['connections'] ?? [];

        $settings['default'] = [
            'config_name' => 'queue.default',
            'value'       => $data['default'] ?? 'public',
            'is_public'   => 0,
        ];

        if (is_array($connections)) {
            foreach ($connections as $key => $values) {
                $name = sprintf('connections.%s', $key);

                $settings[$name] = [
                    'config_name' => 'queue.connections.' . $key,
                    'value'       => $values,
                    'is_auto'     => 1,
                    'is_public'   => 0,
                ];
            }
        }

        return $settings;
    }
}
