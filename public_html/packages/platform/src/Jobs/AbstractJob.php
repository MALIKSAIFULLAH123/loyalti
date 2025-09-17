<?php

namespace MetaFox\Platform\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use MetaFox\Platform\Facades\Settings;

abstract class AbstractJob implements ShouldQueue
{
    public int $timeout = 900;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(...$arguments)
    {
        $this->timeout = Settings::get('queue.retry_timeout', $this->timeout);
    }

    public function retryAfter(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

}
