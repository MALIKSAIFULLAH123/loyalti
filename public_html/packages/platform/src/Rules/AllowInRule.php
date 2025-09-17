<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

/**
 * Class AllowInRule.
 */
class AllowInRule implements Rule
{
    /**
     * AllowInRule constructor.
     *
     * @param array<int, mixed> $allows
     */
    public function __construct(protected array $allows, protected ?string $message = null)
    {
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
        return in_array($value, $this->getAllows());
    }

    /**
     * @return array<int, mixed>
     */
    public function getAllows(): array
    {
        return $this->allows;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        if (null !== $this->message) {
            return $this->message;
        }

        return __p('validation.in_array', ['other' => implode(', ', $this->getAllows())]);
    }

    /**
     * Support generating API documents.
     *
     * @return array
     */
    public function docs(): array
    {
        return [
            'setter' => (fn() => Arr::random($this->getAllows())),
            'description' => $this->getDocDescription(),
        ];
    }

    /**
     * Get the document description.
     *
     * @return string
     */
    private function getDocDescription(): string
    {
        $allows = $this->getAllows();
        if (empty($allows)) {
            return '';
        }

        if (count($allows) === 1) {
            return sprintf('Must be <code>%s</code>', end($allows));
        }

        return sprintf('Must be one of <code>%s</code>', implode('</code>, <code>', $allows));
    }
}
