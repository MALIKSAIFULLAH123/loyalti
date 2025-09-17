<?php

namespace MetaFox\HealthCheck\Checks;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckCache extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();

        $result->success(__p('health-check::phrase.using_cache_driver', ['value' => config('cache.default')]));
        $key = __METHOD__;

        $input = Str::random(5);

        Cache::set($key, $input);

        $output = Cache::get($key);

        if ($input !== $output) {
            $result->error(__p('health-check::phrase.cache_does_not_work_property'));
        }

        return $result;
    }

    public function getName()
    {
        return __p('health-check::phrase.cache');
    }
}
