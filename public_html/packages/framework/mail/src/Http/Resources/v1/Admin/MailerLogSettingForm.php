<?php

namespace MetaFox\Mail\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use MetaFox\Form\AbstractForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Mail\Mails\VerifyConfig;
use MetaFox\Platform\Facades\Settings;

/**
 * @driverType mailer
 * @driverName log
 */
class MailerLogSettingForm extends Form
{
    protected function prepare(): void
    {
        $vars = [
            'mail.mailers.log.channel',
            'mail.test_email',
            'mail.from',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        // force set value
        Arr::set($value, 'mail.mailers.log.transport', 'log');

        if (!Arr::get($value, 'mail.mailers.log.channel')) {
            Arr::set($value, 'mail.mailers.log.channel', 'daily');
        }

        $this->title(__p('mail::phrase.mailer_log_settings'))
            ->action('admincp/setting/mail')
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::choice('mail.mailers.log.channel')
                ->required()
                ->label(__p('mail::phrase.log_channel_label'))
                ->options($this->getLogChannelOptions())
                ->description(__p('mail::phrase.log_channel_desc')),
        );

        $this->addDefaultFooter(true);
    }

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

    public function validated(Request $request): array
    {
        $data = $request->validate([
            'mail.mailers.log.channel' => 'required|string',
        ]);

        Arr::set($data, 'mail.mailers.log.transport', 'log');

        config([
            'mail.mailers.verify_config' => Arr::get($data, 'mail.mailers.log'),
        ]);

        Mail::mailer('verify_config')
            ->send(new VerifyConfig(Arr::get($data, 'mail')));

        return $data;
    }
}
