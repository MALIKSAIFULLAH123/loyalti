<?php

namespace MetaFox\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class DateEqualOrAfterRule implements Rule
{
    private Carbon $target;

    private string $message;

    /**
     * This rule is used for validating if the current datetime value is equal or after the datetime target.
     *
     * @param Carbon|null $target  The target to compare to. If null provided, the current value will be compared to now.
     * @param string|null $message A custom message if current value fails the valuation.
     */
    public function __construct(?Carbon $target = null, ?string $message = null)
    {
        if (!$target instanceof Carbon) {
            $target = Carbon::now();
        }

        if (!is_string($message)) {
            $message = __p('validation.after_or_equal', ['date' => $target->toDateTimeString()]);
        }

        $this->target = $target;
        $this->message = $message;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        $currentDate = Carbon::parse($value);

        if (!$currentDate instanceof Carbon) {
            return false;
        }

        return $currentDate->greaterThanOrEqualTo($this->target());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return Carbon
     */
    public function target(): Carbon
    {
        return $this->target;
    }

    /**
     * @param  Carbon               $target
     * @return DateEqualOrAfterRule
     */
    public function setTarget(Carbon $target): self
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @param  string               $message
     * @return DateEqualOrAfterRule
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
