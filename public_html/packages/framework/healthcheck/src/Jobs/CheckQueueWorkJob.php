<?php

namespace MetaFox\HealthCheck\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use MetaFox\Core\Models\SiteSetting;

class CheckQueueWorkJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    public function __construct() {}

    public function handle(): void
    {
        $settings = SiteSetting::query()->where('name', 'health-check.latest_heartbeat_at')->first();
        $queue    = $this->queue ?? 'default';

        $settings?->update([
            'value_actual' => [$queue => now()->timestamp],
        ]);
    }
}
