<?php

namespace MetaFox\Captcha\Http\Resources\v1\Admin;

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
 * Class SiteSettingForm.
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @property Model $resource
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $vars = [
            'captcha.default',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->asPost()
            ->title(__p('captcha::phrase.settings'))
            ->action('admincp/setting/captcha')
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::dropdown('captcha.default')
                ->label(__p('captcha::admin.captcha_type_label'))
                ->description(__p('captcha::admin.captcha_type_description'))
                ->options(Captcha::getOptions())
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
            );
    }
}
