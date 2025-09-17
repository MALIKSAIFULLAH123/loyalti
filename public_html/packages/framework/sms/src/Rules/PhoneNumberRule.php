<?php

namespace MetaFox\Sms\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use MetaFox\Sms\Contracts\ManagerInterface;
use MetaFox\Sms\Support\Traits\PhoneNumberTrait;

class PhoneNumberRule implements Rule
{
    use PhoneNumberTrait;

    public function __construct(
        private ?string $service = null,
    ) {
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
        if (empty($value)) {
            return false;
        }

        try {
            if (empty($this->service)) {
                // use the default validation logic
                return $this->validatePhoneNumber($value);
            }

            // use service's validator
            return resolve(ManagerInterface::class)
                ->service($this->service)
                ->validatePhoneNumber($value);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __p('sms::validation.phone_number.international_format');
    }
}
