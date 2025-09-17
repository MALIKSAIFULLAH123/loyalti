<?php

namespace MetaFox\Mail\HealthCheck;

use Illuminate\Support\Facades\Mail;
use MetaFox\Mail\Mails\HealthCheck;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckMailSender extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();

        try {
            $sender = config('mail.default');
            $result->debug(__p('mail::phrase.send_mail_method', ['value' => config('mail.default')]));

            if ($sender === 'log') {
                $result->error(__p('mail::phrase.current_mail_sender', ['value' => $sender]));
            }

            if (!config('mail.from.address') || !config('mail.from.name')) {
                $result->error(__p('mail::phrase.missed_mail_settings_configuration', ['url' => '/admincp/mail/setting']));
            } else {
                $result->debug(__p('mail::phrase.send_mail_from_name', [
                    'name'  => config('mail.from.name'),
                    'email' => config('mail.from.address'),
                ]));
            }

            if (!config('mail.from.address')) {
                $result->error(__p('mail::phrase.missing_mail_from_address'));
            }
            if (!config('mail.from.name')) {
                $result->error(__p('mail::phrase.missing_mail_from_name'));
            }

            Mail::to(config('app.site_email'))->send(new HealthCheck());
        } catch (\Exception $exception) {
            $result->error(sprintf($exception->getMessage()));
        }
        // try to send mail directly.
        return $result;
    }

    public function getName()
    {
        return __p('mail::phrase.send_mail_methods');
    }
}
