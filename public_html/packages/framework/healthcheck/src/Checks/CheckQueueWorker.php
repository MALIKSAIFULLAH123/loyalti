<?php

namespace MetaFox\HealthCheck\Checks;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckQueueWorker extends Checker
{
    protected int $failWhenTestJobTakesLongerThanMinutes = 5;

    public function check(): Result
    {
        $queues = ['default'];
        $result = $this->makeResult();

        $result->debug(__p('queue::phrase.default_label') . ': ' . config('queue.default', 'database'));

        $settings = SiteSetting::query()->where('name', 'health-check.latest_heartbeat_at')->first();
        $values   = $settings?->getValue() ?? [];

        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($queues as $queue) {
            $lastHeartbeatTimestamp = Arr::get($values, $queue);

            if (empty($lastHeartbeatTimestamp)) {
                $result->error(__p('health-check::phrase.the_queue_did_not_run_yet', ['queue' => $queue]));
                continue;
            }

            $latestHeartbeatAt = Carbon::createFromTimestamp($lastHeartbeatTimestamp);

            $minutesAgo = $latestHeartbeatAt->diffInMinutes() + 1;

            if ($minutesAgo > $this->failWhenTestJobTakesLongerThanMinutes) {
                $result->error(__p('health-check::phrase.the_last_run_of_the_queue_was_more_than_minutes_ago', [
                    'queue'      => $queue,
                    'minutesAgo' => $minutesAgo,
                ]));
                continue;
            }

            // pass
            $result->success(__p('health-check::phrase.the_last_run_of_the_queue_was_more_than_minutes_ago', [
                'queue'      => $queue,
                'minutesAgo' => $minutesAgo,
            ]));
        }

        return $result;
    }

    public function getName()
    {
        return __p('health-check::phrase.queues');
    }
}
