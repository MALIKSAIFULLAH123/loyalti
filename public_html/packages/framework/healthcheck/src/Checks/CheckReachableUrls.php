<?php

namespace MetaFox\HealthCheck\Checks;

use Illuminate\Support\Facades\Http;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckReachableUrls extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();
        $urls   = [
            'https://api.facebook.com',
            // 'https://cloudcall-s01.phpfox.com/build-service/ping'
        ];

        foreach ($urls as $url) {
            try {
                $response = Http::timeout(10)
                    ->retry(2)
                    ->get($url);

                if ($response->successful()) {
                    $result->success(__p('health-check::phrase.reached_url', ['url' => $url]));
                }
            } catch (\Exception $exception) {
                $result->error(__p('health-check::phrase.failed_pinging_url_exception_message', [
                    'url'     => $url,
                    'message' => $exception->getMessage(),
                ]));
            }
        }

        return $result;
    }

    public function getName()
    {
        return __p('health-check::phrase.reachable_urls');
    }
}
