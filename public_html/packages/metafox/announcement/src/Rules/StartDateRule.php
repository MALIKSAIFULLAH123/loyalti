<?php

namespace MetaFox\Announcement\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class StartDateRule implements
    Rule,
    DataAwareRule
{
    /**
     * @var array
     */
    protected array $data = [];

    public function passes($attribute, $value)
    {
        $startDate   = Carbon::parse(Arr::get($this->data, 'start_date'));
        $currentDate = Carbon::parse(Carbon::now()->toISOString());

        if ($startDate->lessThan($currentDate)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return __p('announcement::validation.start_time_must_be_greater_than_or_equal_to_current_time');
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
