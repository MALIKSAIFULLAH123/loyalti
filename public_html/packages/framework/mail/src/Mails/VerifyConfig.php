<?php

namespace MetaFox\Mail\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

/**
 * stub: packages/mails/mail.stub.
 */

/**
 * Class VerifyConfig.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class VerifyConfig extends Mailable
{
    use Queueable;
    use SerializesModels;

    private array $config = [];

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to(Arr::get($this->config, 'test_email'))
            ->from(Arr::get($this->config, 'from.address'), Arr::get($this->config, 'from.name'))
            ->view('verify_mailer_config');
    }
}
