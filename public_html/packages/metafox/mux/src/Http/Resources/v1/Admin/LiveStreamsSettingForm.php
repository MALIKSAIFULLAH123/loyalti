<?php

namespace MetaFox\Mux\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm;
use MetaFox\Form\Builder;
use MetaFox\Mux\Support\Providers\Mux;
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
class LiveStreamsSettingForm extends AdminSettingForm
{
    protected function prepare(): void
    {
        $module = 'mux.livestreaming';
        $vars   = [
            'mux.livestreaming.client_id',
            'mux.livestreaming.client_secret',
            'mux.livestreaming.webhook_secret',
            'mux.livestreaming.reduced_latency',
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
                Builder::linkButton('mux.livestreaming.back_to_setting')
                    ->link('/livestreaming/streaming-service/browse')
                    ->variant('link')
                    ->sizeNormal()
                    ->color('primary')
                    ->setAttribute('controlProps', ['sx' => ['display' => 'block']])
                    ->label(__p('mux::phrase.back_to_streaming_service')),
                Builder::text('mux.livestreaming.client_id')
                    ->required()
                    ->label(__p('mux::phrase.mux_client_id'))
                    ->description(__p('mux::phrase.mux_client_id_description'))
                    ->yup(Yup::string()->required()),
                Builder::password('mux.livestreaming.client_secret')
                    ->required()
                    ->marginNormal()
                    ->label(__p('mux::phrase.mux_client_secret'))
                    ->description(__p('mux::phrase.mux_client_secret_description'))
                    ->yup(Yup::string()->required()),
                Builder::text('mux.livestreaming.webhook_secret')
                    ->required()
                    //                    ->marginNormal()
                    ->label(__p('mux::phrase.mux_webhook_secret'))
                    ->description(__p('mux::phrase.mux_webhook_secret_description', [
                        'muxLink'        => Mux::MUX_WEBHOOK_SETTING_PATH,
                        'muxCallbackUrl' => apiUrl('live-video.callback', ['provider' => 'mux'], true),
                    ]))
                    ->yup(Yup::string()->required()),
                Builder::switch('mux.livestreaming.reduced_latency')
                    ->label(__p('mux::phrase.mux_reduced_latency'))
                    ->description(__p('mux::phrase.mux_reduced_latency_desc'))
            );

        $this->addDefaultFooter(true);
    }

    public function alertMessage($modified = []): ?string
    {
        if (!$settings = Arr::get($modified, 'all')) {
            return null;
        }

        if (!Arr::except($settings, ['mux.livestreaming.reduced_latency'])) {
            return null;
        }

        if ($this->shouldAlertToRebuildMobile()) {
            return $this->rebuildMobileMessage();
        }

        return $this->rebuildMessage();
    }
}
