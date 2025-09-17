<?php
namespace MetaFox\EMoney\Http\Requests\v1\UserBalance\Admin;

use MetaFox\EMoney\Facades\UserBalance;

class ReduceRequest extends SendRequest
{
    protected function getMinAmount(float $currentBalance): float
    {
        return UserBalance::getMinValueForReducing($currentBalance);
    }

    protected function getMaxAmount(float $currentBalance): float
    {
        return UserBalance::getMaxValueForReducing($currentBalance);
    }
}
