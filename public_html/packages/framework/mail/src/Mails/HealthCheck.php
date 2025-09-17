<?php

namespace MetaFox\Mail\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * stub: packages/mails/mail.stub.
 */

/**
 * Class VerifyConfig.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class HealthCheck extends Mailable
{
    use Queueable;
    use SerializesModels;


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('verify_mailer_config');
    }
}
