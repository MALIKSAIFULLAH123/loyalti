<?php

namespace MetaFox\Broadcast\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
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
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $vars = [
            'broadcast.default_connection',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/broadcast')
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        // $this->addBasic()
        //     ->addFields(
        //         Builder::dropdown('broadcast.default_connection')
        //         ->label(__p('broadcast::phrase.default_connection'))
        //         ->description(__p('broadcast::phrase.default_connection_desc'))
        //         ->options($this->getBroadcastDriverOptions())
        //     );

        // $this->addDefaultFooter(true);
    }

    /**
     * getBroadcastDriverOptions
     *
     * @return array<mixed>
     */
    private function getBroadcastDriverOptions(): array
    {
        $response  = [];
        $conntions = config('broadcasting.connections', []);

        foreach ($conntions as $key => $value) {
            $response[] = ['value' => $key, 'label' => $key];
        }

        return $response;
    }
}
