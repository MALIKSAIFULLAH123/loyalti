<?php

namespace MetaFox\Platform\Support;

use Illuminate\Support\Facades\Log;

class PhpProfiling
{
    /**
     * @var array<string,int>
     */
    private array $store = [];

    /**
     * @var array<string,int>
     */
    private array $counter = [];

    /**
     * @var array<string,int>
     */
    private array $summary = [];

    public const TOTAL_LABEL = 'total request';

    private int $startedAt;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct()
    {
        $this->startedAt = microtime(true);
        $this->logger    = Log::channel('profiler');
    }

    /**
     * @param  string $label
     * @return void
     */
    public function tick(string $label)
    {
        if (!isset($this->counter[$label])) {
            $this->counter[$label] = 0;
        }

        $this->counter[$label] = $this->counter[$label] + 1;
        $this->store[$label]   = microtime(true);
    }

    /**
     * @param  string $label
     * @return void
     */
    public function end(string $label)
    {
        $start = array_key_exists($label, $this->store) ? $this->store[$label] : $this->startedAt;

        if (!isset($this->summary[$label])) {
            $this->summary[$label] = 0.00000;
        }

        $this->summary[$label] = $this->summary[$label] + microtime(true) - $start;
    }

    public function log(string $message, array $context  = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function dump()
    {
        $total = microtime(true) - $this->startedAt;

        foreach ($this->summary as $label => $spend) {
            $this->logger->debug(
                sprintf(
                    '%s called %s times, costs %.2f ms (%.2f%%)',
                    $label,
                    number_format($this->counter[$label] ?? 1),
                    1000 * $spend,
                    100 * $spend / $total,
                )
            );
        }
    }
}
