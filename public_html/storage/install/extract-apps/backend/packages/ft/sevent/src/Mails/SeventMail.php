<?php

namespace Foxexpert\Sevent\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;

/**
 * stub: packages/mails/mail.stub.
 */
/**
 * Class SeventMail.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class SeventMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** @var array<mixed> */
    private array $config = [];

    /**
     * @param array<mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge($config, [
            'from' => Settings::get('mail.from.address'),
            'name' => Settings::get('mail.from.name'),
        ]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from = Arr::get($this->config, 'from');
        $name = Arr::get($this->config, 'name');
        $html = Arr::get($this->config, 'html');

        return $this->subject(Arr::get($this->config, 'subject'))
            ->from($from, $name)
            ->html($html);
    }
}
