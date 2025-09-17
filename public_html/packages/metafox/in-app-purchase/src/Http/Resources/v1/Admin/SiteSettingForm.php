<?php

namespace MetaFox\InAppPurchase\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'in-app-purchase';
        $vars   = [
            'in-app-purchase.enable_iap_ios',
            'in-app-purchase.enable_iap_android',
            'in-app-purchase.enable_iap_sandbox_mode',
            'in-app-purchase.google_android_package_name',
            'in-app-purchase.apple_app_id',
            'in-app-purchase.apple_key_id',
            'in-app-purchase.apple_issuer_id',
            'in-app-purchase.apple_private_key',
            'in-app-purchase.apple_bundle_id',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic
            ->addFields(...InAppPur::getSettingFormFields());

        $this->addDefaultFooter(true);
    }
}
