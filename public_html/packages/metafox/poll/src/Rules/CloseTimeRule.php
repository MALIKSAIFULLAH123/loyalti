<?php

namespace MetaFox\Poll\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationRuleParser;
use MetaFox\Core\Rules\DateEqualOrAfterRule;

class CloseTimeRule implements Rule, DataAwareRule
{
    /**
     * @var array
     */
    private array $data;

    /**
     * @var string|null
     */
    private ?string $message = null;

    public function passes($attribute, $value): bool
    {
        $enableClose = Arr::get($this->data, 'enable_close', 0);

        if (!$enableClose) {
            return true;
        }

        $rules = ['close_time' => ['required', 'date', new DateEqualOrAfterRule(Carbon::now())]];

        $messages = [
            DateEqualOrAfterRule::class => __p('poll::phrase.the_close_time_should_be_greater_than_the_current_time'),
        ];

        if (is_string($requiredRuleName = $this->getRuleName('date'))) {
            Arr::set($messages, $requiredRuleName, __p('validation.date', ['attribute' => __p('poll::phrase.close_time')]));
        }

        $validator = Validator::make([
            'close_time' => $value,
        ], $rules);

        $valid = $validator->passes();

        if (!$valid) {
            $failed = $validator->failed();

            $failed = array_shift($failed);

            if (is_array($failed)) {
                $ruleName = array_key_first($failed);

                if (Arr::has($messages, $ruleName)) {
                    $this->message = $messages[$ruleName];
                }
            }
        }

        return $valid;
    }

    protected function getRuleName(string $rule): ?string
    {
        $parts = ValidationRuleParser::parse($rule);

        if (!is_array($parts)) {
            return null;
        }

        $name = array_shift($parts);

        if (!is_string($name)) {
            return null;
        }

        return $name;
    }

    public function message()
    {
        return $this->message ?? __p('poll::phrase.close_time_is_a_required_field');
    }

    public function setData($data)
    {
        if (!is_array($data)) {
            return;
        }

        $this->data = $data;
    }

    public function docs(): array
    {
        return [
            'type' => 'date',
        ];
    }
}
