<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Sms\Listeners;

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
    /**
     * injectSmsConfig.
     *
     * @param  array<mixed> $settings
     * @return void
     */
    private function injectSmsConfig(array &$settings): void
    {
        // check for installation only.
        $data = app('files')->getRequire(base_path('config/sms.php'));
        // load original config
        $services = $data['services'] ?? [];

        $settings['default'] = [
            'config_name' => 'sms.default',
            'value'       => $data['default'] ?? 'array',
            'is_public'   => 0,
        ];

        $settings['test_number'] = [
            'config_name' => 'sms.test_number',
            'env_var'     => 'MFOX_SMS_TEST_NUMBER',
            'value'       => '',
            'is_public'   => 0,
        ];

        if (is_array($services)) {
            foreach ($services as $key => $values) {
                $name = sprintf('services.%s', $key);

                $settings[$name] = [
                    'config_name' => 'sms.services.' . $key,
                    'value'       => $values,
                    'is_auto'     => 1,
                    'is_public'   => 0,
                ];
            }
        }
    }

    public function getSiteSettings(): array
    {
        $settings = app('files')->getRequire(base_path('packages/framework/sms/resources/settings.php'));

        $this->injectSmsConfig($settings);

        return $settings;
    }
}
