<?php

namespace MetaFox\Captcha\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Core\Models\SiteSetting as Model;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class RecaptchaV3SettingForm.
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @property Model $resource
 */
class RecaptchaV3SettingForm extends Form
{
    /**
     * @var string
     */
    protected string $driver;

    public function boot(string $driver): void
    {
        $this->driver = $driver;
    }

    protected function prepare(): void
    {
        $vars = [
            'captcha.recaptcha_v3.site_key',
            'captcha.recaptcha_v3.secret',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->asPut()
            ->title(__p('captcha::phrase.settings'))
            ->action('admincp/captcha/type/' . $this->driver)
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::text('captcha.recaptcha_v3.site_key')
                ->label(__p('captcha::phrase.recaptcha_site_key'))
                ->description(__p('captcha::phrase.recaptcha_description'))
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('captcha.recaptcha_v3.secret')
                ->label(__p('captcha::phrase.recaptcha_secret'))
                ->description(__p('captcha::phrase.recaptcha_secret_description'))
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit(),
                Builder::cancelButton()
            );
    }

    public function validated(Request $request): array
    {
        return $request->validate([
            'captcha.recaptcha_v3.site_key' => 'required|string',
            'captcha.recaptcha_v3.secret'   => 'required|string',
        ]);
    }
}
