<?php

namespace MetaFox\HealthCheck\Checks;

use Illuminate\Support\Facades\Log;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckLogging extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();

        $channels = [config('logging.default')];

        foreach ($channels as $channel) {
            try {
                Log::channel($channel)
                    ->debug('Checking if logs are writable - this message is logging by Health Check');

                $result->success(__p('health-check::phrase.log_channel_value_is_available', ['value' => $channel]));
            } catch (\Exception $exception) {
                $result->error(__p('health-check::phrase.failed_logging_to_channel_exception_message', [
                    'channel' => $channel,
                    'message' => $exception->getMessage(),
                ]));
            }
        }

        return $result;
    }

    public function getName()
    {
        return __p('health-check::phrase.logging');
    }
}
