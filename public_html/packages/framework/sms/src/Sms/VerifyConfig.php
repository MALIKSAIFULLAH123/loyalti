<?php

namespace MetaFox\Sms\Sms;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

/**
 * stub: packages/smss/sms.stub.
 */

/**
 * Class VerifyConfig.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class VerifyConfig
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
        // return $this->to(Arr::get($this->config, 'test_number'))
        //     ->view('verify_service_config');
    }
}
