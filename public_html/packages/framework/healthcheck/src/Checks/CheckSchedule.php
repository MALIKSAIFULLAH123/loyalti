<?php

namespace MetaFox\HealthCheck\Checks;

use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckSchedule extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();

        return $result;
    }

    public function getName()
    {
        return __p('health-check::phrase.scheduler');
    }
}
