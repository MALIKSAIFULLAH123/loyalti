<?php

namespace MetaFox\Profile\Support\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use MetaFox\Profile\Http\Resources\v1\Field\Validation\UrlField;
use MetaFox\Profile\Support\CustomField;

class UrlRule implements Rule
{
    public function __construct(protected array $match)
    {
    }

    protected string $message = 'validation.url';

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
        $valueHost = parse_url($value, PHP_URL_HOST);
        $type      = Arr::get($this->match, 'url_rule_type');

        if (!is_string($value)) {
            return false;
        }

        if (empty($type)) {
            return true;
        }

        $fieldName = sprintf(CustomField::FIELD_NAME_ERROR_MESSAGE, 'url_rule_type');
        $keyPhrase = sprintf(CustomField::KEY_PHRASE_VALIDATION_MESSAGE, $attribute, $fieldName);
        $this->setMessage($keyPhrase);

        return match ($type) {
            UrlField::TYPE_DISALLOWED => !$this->checked($attribute, $valueHost),
            UrlField::TYPE_ALLOWED    => $this->checked($attribute, $valueHost),
            UrlField::NOT_APPLIED     => true,
        };
    }

    protected function checked($attribute, $valueHost): bool
    {
        $matches = Arr::get($this->match, 'url_rule_values', []);

        if (empty($matches)) {
            return true;
        }

        foreach ($matches as $match) {
            if (0 == strcasecmp($valueHost, $match)) {
                return true;
            }
        }

        return false;
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
