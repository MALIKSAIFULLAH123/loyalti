<?php

namespace MetaFox\Socialite\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 *
 * @codeCoverageIgnore
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'socialite';
        $vars   = [
            'socialite.prompt_users_to_set_passwords',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::switch('socialite.prompt_users_to_set_passwords')
                    ->label(__p('socialite::phrase.prompt_users_to_set_passwords_label'))
                    ->description(__p('socialite::phrase.prompt_users_to_set_passwords_desc'))
            );

        $this->addDefaultFooter(true);
    }
}
