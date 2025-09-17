<?php

namespace MetaFox\Sms\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SiteSettingForm.
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $vars = [
            'sms.default',
        ];

        $values = [];

        foreach ($vars as $var) {
            Arr::set($values, $var, Settings::get($var));
        }

        $this->title(__p('sms::phrase.sms_server_settings'))
            ->action('admincp/setting/sms')
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::choice('sms.default')
                ->required()
                ->label(__p('sms::phrase.sms_send_method_label'))
                ->autoComplete('off')
                ->description(__p('sms::phrase.sms_send_method_desc'))
                ->options($this->getServiceOptions()),
        );

        $this->addDefaultFooter(true);
    }

    /**
     * @return array<mixed>
     */
    protected function getServiceOptions()
    {
        $service = Settings::get('sms.services', []);
        $options = [];

        foreach ($service as $name => $value) {
            $options[] = [
                'label' => $name,
                'value' => $name,
            ];
        }

        return $options;
    }
}
