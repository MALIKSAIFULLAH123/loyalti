<?php

namespace MetaFox\Twilio\Sms;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MetaFox\Sms\Support\Message;

/**
 * stub: packages/smss/sms.stub.
 */

/**
 * Class VerifyConfig.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class VerifyConfig extends Message
{
    use Queueable;
    use SerializesModels;

    private array $config = [];

    /**
     * @param array<mixed> $config
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
        $content = __p('twilio::phrase.verify_twilio_config');

        return $this->setRecipients(Arr::get($this->config, 'test_number'))
            ->setContent($content)->setUrl(null);
    }
}
