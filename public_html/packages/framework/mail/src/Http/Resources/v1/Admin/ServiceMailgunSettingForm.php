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
 * @driverName mailgun
 */
class ServiceMailgunSettingForm extends Form
{
    protected function prepare(): void
    {
        $vars = [
            'core.services.mailgun.domain',
            'core.services.mailgun.secret',
            'core.services.mailgun.endpoint',
            'mail.test_email',
            'mail.from',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('mail::phrase.mailer_mailgun_settings'))
            ->action('admincp/mail/mailer/mailgun/mailgun')
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('core.services.mailgun.domain')
                ->label(__p('mail::mailgun.domain_label'))
                ->description(__p('mail::mailgun.mailgun_domain_desc'))
                ->required()
                ->yup(Yup::string()->label(__p('mail::mailgun.domain_label'))->required()),
            Builder::text('core.services.mailgun.secret')
                ->required()
                ->label(__p('mail::mailgun.secret_label'))
                ->description(__p('mail::mailgun.secret_desc'))
                ->yup(Yup::string()->required()->nullable()),
            Builder::text('core.services.mailgun.endpoint')
                ->label(__p('mail::mailgun.mailgun_endpoint_label'))
                ->description(__p('mail::mailgun.endpoint_desc'))
                ->placeholder('api.mailgun.net')
                ->yup(Yup::string()->label(__p('mail::mailgun.mailgun_endpoint_label'))->required()->nullable()),
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

    /**
     * @param Request $request
     *
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        $data = $request->validate([
            'core.services.mailgun.domain'   => 'string|required',
            'core.services.mailgun.secret'   => 'string|required',
            'core.services.mailgun.endpoint' => 'string|required',
            'mail.from.address'              => 'string|required',
            'mail.from.name'                 => 'string|required',
            'mail.test_email'                => 'string|required',
        ]);

        Arr::set($data, 'mail.mailers.mailgun.transport', 'mailgun');

        config([
            'services.mailgun'           => Arr::get($data, 'core.services.mailgun'),
            'mail.mailers.verify_config' => Arr::get($data, 'mail.mailers.mailgun'),
        ]);

        Mail::mailer('verify_config')
            ->send(new VerifyConfig(Arr::get($data, 'mail')));

        return $data;
    }
}
