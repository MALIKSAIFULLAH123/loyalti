<?php

namespace MetaFox\Platform\HealthCheck;

class Checker
{

    protected function makeResult(): Result
    {
        return new Result();
    }

    public function getName()
    {
        return __CLASS__;
    }

    public function check(): Result
    {
        $result = $this->makeResult();

        $result->success(sprintf('%s comming soon!', __METHOD__));

        return $result;
    }
}