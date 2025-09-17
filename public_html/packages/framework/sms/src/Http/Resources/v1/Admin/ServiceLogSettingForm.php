<?php

namespace MetaFox\Sms\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
 * @driverType service
 * @driverName log
 */
class ServiceLogSettingForm extends Form
{
    protected function prepare(): void
    {
        $vars = [
            'sms.services.log.channel',
            'sms.test_number',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        // force set value
        Arr::set($value, 'sms.services.log.service', 'log');

        if (!Arr::get($value, 'sms.services.log.channel')) {
            Arr::set($value, 'sms.services.log.channel', 'daily');
        }

        $this->title(__p('sms::phrase.service_log_settings'))
            ->action('admincp/sms/service/log')
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::choice('sms.services.log.channel')
                ->required()
                ->label(__p('sms::phrase.log_channel_label'))
                ->options($this->getLogChannelOptions())
                ->description(__p('sms::phrase.log_channel_desc')),
        );

        $this->addDefaultFooter(true);
    }

    /**
     * getLogChannelOptions.
     *
     * @return array<mixed>
     */
    private function getLogChannelOptions(): array
    {
        $result = [];

        $channels = config('logging.channels', []);

        foreach ($channels as $value => $option) {
            if (!isset($option['selectable']) || !isset($option['label'])) {
                continue;
            }
            $result[] = ['value' => $value, 'label' => $option['label']];
        }

        return $result;
    }

    /**
     * validated.
     *
     * @param  Request      $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        $data = $request->validate([
            'sms.services.log.channel' => 'required|string',
        ]);

        Arr::set($data, 'sms.services.log.service', 'log');

        return $data;
    }
}
