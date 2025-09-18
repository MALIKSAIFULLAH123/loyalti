<?php

namespace MetaFox\Invite\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Sms\Support\Traits\PhoneNumberTrait;

class StoreInviteRule implements Rule
{
    use PhoneNumberTrait;

    /**
     * AllowInRule constructor.
     */
    public function __construct()
    {
    }

    protected string $message;

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
        $arrayInvalid          =  [];
        foreach ($value as $item) {
            if ($this->validatePhoneNumber($item)) {
                continue;
            }

            if (filter_var($item, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $arrayInvalid[] = $item;
        }

        if (!empty($arrayInvalid)) {
            $this->setMessage(__p('invite::validation.value_invalid_email_or_phone_number_format', [
                'value' => implode(', ', $arrayInvalid),
            ]));

            return false;
        }

        return true;
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

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
