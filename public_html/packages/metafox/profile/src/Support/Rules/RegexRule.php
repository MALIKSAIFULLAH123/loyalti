<?php

namespace MetaFox\Profile\Support\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Profile\Support\CustomField;

class RegexRule implements Rule
{
    public function __construct(protected string $type, protected string $match)
    {
    }

    protected string $message = 'validation.regex';

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
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
        $fieldName = sprintf(CustomField::FIELD_NAME_ERROR_MESSAGE, $this->type);
        $keyPhrase = sprintf(CustomField::KEY_PHRASE_VALIDATION_MESSAGE, $attribute, $fieldName);

        $this->setMessage($keyPhrase);

        return (bool)preg_match("/$this->match/", $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return htmlspecialchars_decode(__p($this->getMessage()));
    }
}
