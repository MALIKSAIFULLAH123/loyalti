<?php
namespace MetaFox\ActivityPoint\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PointStatistic;
use MetaFox\ActivityPoint\Support\ActivityPoint;

class AdjustPointRule implements ValidationRule, DataAwareRule
{
    /**
     * @var array
     */
    protected mixed $data = null;

    /**
     * @param $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (int) $value;

        if ($value < 1) {
            return;
        }

        if (!is_array($this->data)) {
            return;
        }

        $type = (int) Arr::get($this->data, 'type');

        if (ActivityPoint::TYPE_RETRIEVED !== $type) {
            return;
        }

        $userIds = Arr::get($this->data, 'user_ids');

        if (!is_array($userIds)) {
            return;
        }

        /**
         * Control memory leak for whereIn statement
         */
        if (count($userIds) > 100) {
            return;
        }

        $count = PointStatistic::query()
            ->where('current_points', '>=', $value)
            ->whereIn('id', $userIds)
            ->count();

        if ($count === count($userIds)) {
            return;
        }

        /**
         * @todo Improve this error message later
         */
        $fail(__p('activitypoint::phrase.the_point_you_want_adjust_is_over_maximum_points'));
    }
}
