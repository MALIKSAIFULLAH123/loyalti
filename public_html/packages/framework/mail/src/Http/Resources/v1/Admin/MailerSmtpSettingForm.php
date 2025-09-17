<?php

namespace MetaFox\Mail\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use MetaFox\Form\AbstractForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Mail\Mails\VerifyConfig;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * @driverType mailer
 * @driverName smtp
 */
class MailerSmtpSettingForm extends Form
{
    protected string $driver = 'smtp';

    protected string $name = 'smtp';

    private array $variables = [];

    public function boot(string $driver, string $name)
    {
        $this->driver = $driver;
        $this->name   = $name;
    }

    protected function prepare(): void
    {
        $vars = [
            'mail.mailers.smtp.password',
            'mail.mailers.smtp.port',
            'mail.mailers.smtp.encryption',
            'mail.mailers.smtp.username',
            'mail.mailers.smtp.host',
            'mail.test_email',
            'mail.from',
        ];

        $values = [];

        foreach ($vars as $var) {
            Arr::set($values, $var, Settings::get($var));
        }

        // force set value
        Arr::set($values, 'mail.mailers.smtp.transport', 'smtp');

        $this->title(__p('core::phrase.mailer_smtp_settings'))
            ->action(sprintf('/admincp/mail/mailer/%s/%s', $this->driver, $this->name))
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('mail.mailers.smtp.host')
                ->required()
                ->label(__p('mail::smtp.host_label'))
                ->marginNormal()
                ->description(__p('mail::smtp.host_desc')),
            Builder::text('mail.mailers.smtp.port')
                ->marginNormal()
                ->label(__p('mail::smtp.port_label')),
            Builder::radioGroup('mail.mailers.smtp.encryption')
                ->label(__p('mail::smtp.encryption_label'))
                ->description(__p('mail::smtp.encryption_desc'))
                ->options([
                    ['value' => 'none', 'label' => 'No Secure'], ['value' => 'tls', 'label' => 'TLS'],
                    ['value' => 'ssl', 'label' => 'SSL'],
                ]),
            Builder::text('mail.mailers.smtp.username')
                ->label(__p('mail::smtp.username_label'))
                ->marginNormal(),
            Builder::password('mail.mailers.smtp.password')
                ->marginNormal()
                ->label(__p('mail::smtp.password_label')),
            Builder::text('mail.from.name')
                ->required()
                ->autoComplete('off')
                ->label(__p('mail::phrase.mail_from_label'))
                ->description(__p('mail::phrase.mail_from_desc'))
                ->placeholder('admin'),
            Builder::text('mail.from.address')
                ->required()
                ->autoComplete('off')
                ->label(__p('mail::phrase.mail_from_address_label'))
                ->description(__p('mail::phrase.mail_from_address_desc'))
                ->placeholder('name@your-domain.com'),
            Builder::text('mail.test_email')
                ->required()
                ->autoComplete('off')
                ->label(__p('mail::mailgun.test_email_label'))
                ->yup(Yup::string()
                    ->required()
                    ->nullable()
                ),
        );

        $this->addDefaultFooter(true);
    }

    public function validated(Request $request): array
    {
        $data = $request->validate([
            'mail.mailers.smtp.host'       => 'required|string',
            'mail.mailers.smtp.port'       => 'required|integer',
            'mail.mailers.smtp.encryption' => 'required|string',
            'mail.mailers.smtp.username'   => 'sometimes|string|nullable',
            'mail.mailers.smtp.password'   => 'sometimes|string|nullable',
            'mail.mailers.smtp.transport'  => 'string|required',
            'mail.from.address'            => 'string|required',
            'mail.from.name'               => 'string|required',
            'mail.test_email'              => 'string|required',
        ]);

        Arr::set($data, 'mail.mailers.smtp.transport', 'smtp');

        config([
            'mail.mailers.verify_config' => Arr::get($data, 'mail.mailers.smtp'),
        ]);

        Mail::mailer('verify_config')
            ->send(new VerifyConfig(Arr::get($data, 'mail')));

        return $data;
    }
}
