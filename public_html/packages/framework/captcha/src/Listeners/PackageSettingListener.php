<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Captcha\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Captcha\Support\Facades\Captcha;
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
            'default' => [
                'is_public' => 1,
                'env_var'   => 'MFOX_CAPTCHA_TYPE',
                'value'     => Captcha::getDefaultCaptchaType(),
            ],
        ];

        $configs = app('files')
            ->getRequire(base_path('packages/framework/captcha/resources/settings.php'));

        if (is_array($configs)) {
            $settings = array_merge($settings, $configs);
        }

        return $settings;
    }

    public function getEvents(): array
    {
        return [
            'captcha.resolve' => [
                ImageCaptchaResolveListener::class,
                RecaptchaV3ResolveListener::class,
            ],
            'captcha.options' => [
                OptionListener::class,
            ],
        ];
    }
}
