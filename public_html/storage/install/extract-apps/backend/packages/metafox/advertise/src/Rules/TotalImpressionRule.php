<?php

namespace MetaFox\Advertise\Rules;

use MetaFox\Advertise\Support\Support;

class TotalImpressionRule extends AbstractTotalRule
{
    public function ruleType(): string
    {
        return Support::PLACEMENT_CPM;
    }

    public function typeErrorMessage(int $min): string
    {
        return __p('advertise::validation.total_impressions_must_be_greater_than_or_equal_to_number', ['number' => $min]);
    }

    public function maxNumberErrorMessage(int $max): string
    {
        return __p('advertise::validation.total_impressions_must_be_less_than_or_equal_to_number', ['number' => number_format($max)]);
    }
}
