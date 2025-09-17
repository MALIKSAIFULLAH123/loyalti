<?php

namespace MetaFox\Mux\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Mux\Support\Providers\Mux;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class StorySettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'mux.story';
        $vars   = [
            'mux.story.client_secret',
            'mux.story.client_id',
            'mux.story.webhook_secret',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::linkButton('mux.story.back_to_setting')
                    ->link('/story/service/browse')
                    ->variant('link')
                    ->sizeNormal()
                    ->color('primary')
                    ->setAttribute('controlProps', ['sx' => ['display' => 'block']])
                    ->label(__p('mux::phrase.back_to_services')),
                Builder::text('mux.story.client_id')
                    ->required()
                    ->label(__p('mux::phrase.mux_client_id'))
                    ->description(__p('mux::phrase.mux_client_id_description'))
                    ->yup(Yup::string()->required()),
                Builder::password('mux.story.client_secret')
                    ->required()
                    ->label(__p('mux::phrase.mux_client_secret'))
                    ->description(__p('mux::phrase.mux_client_secret_description'))
                    ->yup(Yup::string()->required()),
                Builder::text('mux.story.webhook_secret')
                    ->required()
                    ->label(__p('mux::phrase.mux_webhook_secret'))
                    ->description(__p('mux::phrase.mux_webhook_secret_description', [
                        'muxLink'        => Mux::MUX_WEBHOOK_SETTING_PATH,
                        'muxCallbackUrl' => apiUrl('story.callback', ['provider' => 'mux'], true),
                    ]))
                    ->yup(Yup::string()->required()),
            );

        $this->addDefaultFooter(true);
    }
}
