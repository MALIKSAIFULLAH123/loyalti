<?php

namespace MetaFox\Socialite\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class TwitterSettingForm.
 * @ignore
 */
class TwitterSettingForm extends BaseSettingForm
{
    protected function prepare(): void
    {
        $vars = [
            'core.services.twitter.client_id',
            'core.services.twitter.client_secret',
            'core.services.twitter.redirect',
        ];
        $value = [];
        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('socialite::twitter.site_settings'))
            ->action('admincp/setting/core')
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('core.services.twitter.client_id')
                    ->label(__p('socialite::twitter.client_id'))
                    ->description(__p('socialite::twitter.client_id_desc'))
                    ->optional(),
                Builder::text('core.services.twitter.client_secret')
                    ->label(__p('socialite::twitter.client_secret'))
                    ->description(__p('socialite::twitter.client_secret_desc'))
                    ->optional(),
                Builder::text('core.services.twitter.redirect')
                    ->label(__p('socialite::twitter.redirect'))
                    ->description(__p('socialite::twitter.redirect_desc'))
                    ->optional(),
                Builder::checkbox('core.services.twitter.login_enabled')
                    ->label(__p('socialite::twitter.login_enabled'))
            );

        $this->addDefaultFooter(true);
    }
}
