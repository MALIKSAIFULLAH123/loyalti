<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mail\Listeners;

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

    private function injectMailConfig(array &$settings): void
    {
        // check for installation only.
        $data = app('files')->getRequire(base_path('config/mail.php'));
        // load original config
        $mailers = $data['mailers'] ?? [];

        $settings['default'] = [
            'config_name' => 'mail.default',
            'value'       => $data['default'] ?? 'public',
            'is_public'   => 0,
        ];

        $settings['from.name'] = [
            'config_name' => 'mail.from.name',
            'env_var'     => 'MFOX_MAIL_FROM_NAME',
            'value'       => '',
            'is_public'   => 0,
        ];

        $settings['from.address'] = [
            'config_name' => 'mail.from.address',
            'env_var'     => 'MFOX_MAIL_FROM_ADDRESS',
            'value'       => '',
            'is_public'   => 0,
        ];

        $settings['test_email'] = [
            'config_name' => 'mail.test_email',
            'env_var'     => 'MFOX_MAIL_TEST_EMAIL',
            'value'       => '',
            'is_public'   => 0,
        ];

        $settings['enable_site_logo'] = [
            'config_name' => 'mail.enable_site_logo',
            'value'       => false,
            'is_public'   => 0,
        ];

        $settings['enable_site_name'] = [
            'config_name' => 'mail.enable_site_name',
            'value'       => true,
            'is_public'   => 0,
        ];

        $settings['primary_background_color'] = [
            'config_name' => 'mail.primary_background_color',
            'value'       => '#edf2f7', // Default value
            'is_public'   => 0,
        ];

        $settings['content_background_color'] = [
            'config_name' => 'mail.content_background_color',
            'value'       => '#ffffff', // Default value
            'is_public'   => 0,
        ];

        $settings['content_text_color'] = [
            'config_name' => 'mail.content_text_color',
            'value'       => '#718096', // Default value
            'is_public'   => 0,
        ];

        $settings['button_background_color'] = [
            'config_name' => 'mail.button_background_color',
            'value'       => '#2d3748', // Default value
            'is_public'   => 0,
        ];

        $settings['button_text_color'] = [
            'config_name' => 'mail.button_text_color',
            'value'       => '#fff', // Default value
            'is_public'   => 0,
        ];

        if (is_array($mailers)) {
            foreach ($mailers as $key => $values) {
                $name = sprintf('mailers.%s', $key);

                $settings[$name] = [
                    'config_name' => 'mail.mailers.' . $key,
                    'value'       => $values,
                    'is_auto'     => 1,
                    'is_public'   => 0,
                ];
            }
        }
    }

    public function getSiteSettings(): array
    {
        $settings = app('files')->getRequire(base_path('packages/framework/mail/resources/settings.php'));

        $this->injectMailConfig($settings);

        return $settings;
    }

    public function getCheckers(): array
    {
        return [
            \MetaFox\Mail\HealthCheck\CheckMailSender::class,
        ];
    }

    public function getEvents(): array
    {
        return [
            'site_settings.updated' => [
                SiteSettingUpdatedListener::class,
            ],
        ];
    }
}
