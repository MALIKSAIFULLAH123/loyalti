<?php

namespace MetaFox\Advertise\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class EndDateRule implements
    Rule,
    ValidatorAwareRule,
    DataAwareRule
{
    /**
     * @var string|null
     */
    protected ?string $message = null;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var ValidatorContract|null
     */
    protected ?ValidatorContract $validator = null;

    public function passes($attribute, $value)
    {
        if (null === $value) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        $startDate = Carbon::parse(Arr::get($this->data, 'start_date'));

        $endDate = Carbon::parse($value);

        if ($endDate->greaterThanOrEqualTo($startDate)) {
            return true;
        }

        return false;
    }

    public function message()
    {
        return $this->message;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;
    }
}
