<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class PrivacyRule implements Rule
{
    protected array $parameters;

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        app('events')->dispatch('platform.rule.override_privacy_rules', [$attribute, $value]);

        /**
         * @deprecated v5.2 refactor parameters
         */
        $validateList = Arr::get($this->parameters, 'validate_privacy_list', true);

        // if privacy is int then check allows.
        if (!is_array($value)) {
            $validator = Validator::make(['list' => $value], [
                'list' => [new PrivacyValidator()],
            ]);

            return $validator->passes();
        }

        if (!$validateList) {
            return true;
        }

        $validator = Validator::make(['list' => $value], [
            'list' => [new PrivacyListValidator()],
        ]);

        return $validator->passes();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.invalid_privacy');
    }
}
