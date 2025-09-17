<?php

namespace MetaFox\Chat\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\AdminSettingForm;
use MetaFox\Form\Builder;
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
class SiteSettingForm extends AdminSettingForm
{
    protected function prepare(): void
    {
        $vars = [
            'key',
            'secret',
            'app_id',
            'options.cluster',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get('broadcast.connections.pusher.' . $var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/chat')
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('key')
                    ->label(__p('broadcast::pusher.key_label'))
                    ->required()
                    ->yup(Yup::string()->required()),
                Builder::text('secret')
                    ->label(__p('broadcast::pusher.secret_label'))
                    ->required()
                    ->yup(Yup::string()->required()),
                Builder::text('app_id')
                    ->label(__p('broadcast::pusher.app_id_label'))
                    ->required()
                    ->yup(Yup::string()->required()),
                Builder::text('options.cluster')
                    ->label(__p('broadcast::pusher.cluster_label'))
                    ->required()
                    ->yup(Yup::string()->required())
            );

        $this->addDefaultFooter(true);
    }

    /**
     * validated.
     *
     * @param  Request      $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        $params = $request->validate([
            'key'             => 'required|string',
            'secret'          => 'required|string',
            'app_id'          => 'required|string',
            'options.cluster' => 'required|string',
        ]);

        // hard put
        $params['driver']  = 'pusher';
        $cluster           = Arr::get($params, 'options.cluster');
        Arr::set($params, 'options.host', sprintf('api-%s.pusher.com', $cluster));

        return [
            'broadcast' => [
                'default_connection' => 'pusher',
                'connections'        => [
                    'pusher' => $params,
                ],
            ],
        ];
    }

    public function alertMessage($modified = []): ?string
    {
        if (!Arr::get($modified, 'all')) {
            return null;
        }

        if ($this->shouldAlertToRebuildMobile()) {
            return $this->rebuildMobileMessage();
        }

        return $this->rebuildMessage();
    }
}
