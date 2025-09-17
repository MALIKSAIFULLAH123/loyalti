<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;

/**
 * Class MetaFoxPasswordValidationRule.
 */
class MetaFoxPasswordValidationRule implements Rule, DataAwareRule
{
    /**
     * @var array
     */
    protected array $data = [];

    public function __construct(protected User $context)
    {
    }

    /**
     * Set the data under validation.
     *
     * @param  array $data
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
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
        return $this->validatePassword($this->data);
    }

    private function validatePassword(array $params)
    {
        $password = Arr::get($params, 'password');

        $context = $this->context;

        return $context->validatePassword($password);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __p('validation.password');
    }
}
