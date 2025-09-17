<?php

namespace MetaFox\Captcha\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Core\Models\SiteSetting as Model;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\PackageManager;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SiteSettingForm.
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @property Model $resource
 */
class RuleSettingForm extends Form
{
    protected array $captchaRules =  [];

    protected function prepare(): void
    {
        $this->captchaRules = PackageManager::discoverSettings('getCaptchaRules');

        $vars = [
            'captcha.recaptcha_v3.site_key',
            'captcha.recaptcha_v3.secret',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        foreach ($this->captchaRules as $packageAlias => $rules) {
            foreach ($rules as $rule) {
                $fieldName = sprintf('captcha.rules.%s.%s', $packageAlias, $rule);
                Arr::set($value, $fieldName, Settings::get($fieldName, false));
            }
        }

        $this->asPost()
            ->title(__p('captcha::phrase.settings'))
            ->action('admincp/setting/captcha')
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->separateBetweenFields();

        // captcha on create
        foreach ($this->captchaRules as $packageAlias => $rules) {
            if (empty($rules)) {
                continue;
            }

            foreach ($rules as $rule) {
                $fieldName = sprintf('captcha.rules.%s.%s', $packageAlias, $rule);
                $basic->addField(
                    Builder::switch($fieldName)
                        ->label(__p(sprintf('%s::phrase.captcha_on_%s', $packageAlias, $rule)))
                );
            }
        }

        $this->addFooter()
            ->addFields(Builder::submit());
    }
}
