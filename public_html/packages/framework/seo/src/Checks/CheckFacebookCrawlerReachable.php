<?php

namespace MetaFox\SEO\Checks;

use Illuminate\Support\Facades\Http;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckFacebookCrawlerReachable extends Checker
{
    public function check(): Result
    {
        $urls = [
            //            config('app.url').'/user',
        ];

        $result = $this->makeResult();

        foreach ($urls as $url) {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'facebookexternalhit/1.1',
                ])->get($url);

            $result->success($response->status());

            if (!$response->successful()) {
                return $result->error(__p('seo::phrase.failed_getting', ['url' => $url]));
            }
        }

        return $result;
    }

    public function getName()
    {
        return __p('seo::phrase.open_graph');
    }
}
