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
 * @driverName failover
 */
class MailerFailoverSettingForm extends Form
{
    protected function prepare(): void
    {
        $vars = [
            'mail.mailers.failover.mailers',
            'mail.test_email',
            'mail.from',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.mailer_failover_settings'))
            ->action('admincp/setting/mail')
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::choice('mail.mailers.failover.mailers')
                ->label(__p('mail::phrase.failover_mailer_label'))
                ->options($this->getMailerOptions())
                ->multiple()
                ->description(__p('mail::phrase.failover_mailer_desc')),
        );

        $this->addDefaultFooter(true);
    }

    private function getMailerOptions(): array
    {
        $result  = [];
        $mailers = config('mail.mailers', []);
        foreach ($mailers as $value => $option) {
            if ($value === 'failover') {
                continue;
            }
            $result[] = ['value' => $value, 'label' => $value];
        }

        return $result;
    }

    public function validated(Request $request): array
    {
        $data = $request->validate([
            'mail.mailers.failover.mailers'   => 'required|array|min:1',
            'mail.mailers.failover.mailers.*' => 'required|string',
        ]);

        Arr::set($data, 'mail.mailers.failover.transport', 'failover');

        config([
            'mail.mailers.verify_config' => Arr::get($data, 'mail.mailers.failover'),
        ]);

        Mail::mailer('verify_config')
            ->send(new VerifyConfig(Arr::get($data, 'mail')));

        return $data;
    }
}
