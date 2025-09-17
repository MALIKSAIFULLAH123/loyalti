<?php

namespace MetaFox\Mail\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use MetaFox\Form\AbstractForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Mail\Mails\VerifyConfig;
use MetaFox\Yup\Yup;

/**
 * @driverType mailer
 * @driverName ses
 */
class ServiceSesSettingForm extends Form
{
    protected function prepare(): void
    {
        $value = [
            'core.services.ses.key'    => config('services.ses.key'),
            'core.services.ses.secret' => config('services.ses.secret'),
            'core.services.ses.region' => config('services.ses.region'),
            'mail.test_email'          => config('mail.test_email'),
            'mail.from.address'        => config('mail.from.address'),
            'mail.from.name'           => config('mail.from.name'),
        ];

        $this->title(__p('core::phrase.mailer_ses_settings'))
            ->action('admincp/setting/mail')
            ->asPost()
            ->setValue(Arr::undot($value));
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('core.services.ses.key')
                ->required()
                ->label(__p('mail::ses.ses_key'))
                ->yup(Yup::string()->required()->nullable()),
            Builder::text('core.services.ses.secret')
                ->required()
                ->label(__p('mail::ses.ses_secret'))
                ->yup(Yup::string()->required()->nullable()),
            Builder::text('core.services.ses.region')
                ->required()
                ->fullWidth(false)
                ->minWidth('400px')
                ->label(__p('mail::ses.ses_region'))
                ->yup(Yup::string()->required()),
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
            'core.services.ses.key'    => 'required|string',
            'core.services.ses.secret' => 'required|string',
            'core.services.ses.region' => 'required|string',
            'mail.from.address'        => 'string|required',
            'mail.from.name'           => 'string|required',
            'mail.test_email'          => 'string|required',
        ]);

        Arr::set($data, 'mail.mailers.ses.tranport', 'ses');

        config([
            'services.ses'               => Arr::get($data, 'core.services.ses'),
            'mail.mailers.verify_config' => Arr::get($data, 'mail.mailers.ses'),
        ]);

        Mail::mailer('verify_config')
            ->send(new VerifyConfig(Arr::get($data, 'mail')));

        return $data;
    }
}
