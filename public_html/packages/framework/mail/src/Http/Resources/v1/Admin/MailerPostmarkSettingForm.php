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
 * @driverName postmark
 */
class MailerPostmarkSettingForm extends Form
{
    protected function prepare(): void
    {
        $vars = [
            'mail.mailers.postmark.token',
            'mail.test_email',
            'mail.from',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        Arr::set($value, 'mail.mailers.postmark.transport', Settings::get('mail.mailers.postmark.transport', 'postmark'));

        $this->title(__p('core::phrase.mailer_postmark_settings'))
            ->action('admincp/mail/mailer/postmark/postmark')
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('mail.mailers.postmark.token')
                ->required()
                ->label(__p('mail::phrase.postmark_token_label'))
                ->description(__p('mail::phrase.postmark_token_desc')),
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
            Builder::hidden('mail.mailers.postmark.transport'),
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
            'mail.mailers.postmark.token' => 'required|string',
            'mail.from.address'           => 'string|required',
            'mail.from.name'              => 'string|required',
            'mail.test_email'             => 'string|required',
        ]);

        Arr::set($data, 'mail.mailers.postmark.transport', 'postmark');

        config([
            'services.postmark'          => Arr::get($data, 'mail.mailers.postmark'),
            'mail.mailers.verify_config' => Arr::get($data, 'mail.mailers.postmark'),
        ]);

        Mail::mailer('postmark')
            ->send(new VerifyConfig(Arr::get($data, 'mail')));

        return $data;
    }
}
