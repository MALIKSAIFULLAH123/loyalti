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
class VideoSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'mux.video';
        $vars   = [
            'mux.video.client_id',
            'mux.video.client_secret',
            'mux.video.webhook_secret',
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
                Builder::linkButton('mux.video.back_to_setting')
                    ->link('/video/service/browse')
                    ->variant('link')
                    ->sizeNormal()
                    ->color('primary')
                    ->setAttribute('controlProps', ['sx' => ['display' => 'block']])
                    ->label(__p('mux::phrase.back_to_services')),
                Builder::text('mux.video.client_id')
                    ->required()
                    ->label(__p('mux::phrase.mux_client_id'))
                    ->description(__p('mux::phrase.mux_client_id_description'))
                    ->yup(Yup::string()->required()),
                Builder::password('mux.video.client_secret')
                    ->required()
                    ->label(__p('mux::phrase.mux_client_secret'))
                    ->description(__p('mux::phrase.mux_client_secret_description'))
                    ->yup(Yup::string()->required()),
                Builder::text('mux.video.webhook_secret')
                    ->required()
                    ->label(__p('mux::phrase.mux_webhook_secret'))
                    ->description(__p('mux::phrase.mux_webhook_secret_description', [
                        'muxLink'        => Mux::MUX_WEBHOOK_SETTING_PATH,
                        'muxCallbackUrl' => apiUrl('video.callback', ['provider' => 'mux'], true),
                    ]))
                    ->yup(Yup::string()->required()),
//                Builder::switch('mux.reduced_latency')
//                    ->label(__p('mux::phrase.mux_reduced_latency'))
//                    ->description(__p('mux::phrase.mux_reduced_latency_desc'))
            );

        $this->addDefaultFooter(true);
    }
}
