<?php

namespace MetaFox\Platform\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class ResourceNameRule.
 */
class ResourceNameRule implements Rule
{
    private int $minLength = MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH;
    private int $maxLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;

    public function __construct(?string $package)
    {
        if ($package) {
            $this->minLength = Settings::get($package . '.minimum_name_length') ?? MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH;
            $this->maxLength = Settings::get($package . '.maximum_name_length') ?? MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;
        }
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
        try {
            if (!is_string($value)) {
                return false;
            }

            $length = mb_strlen(trim($value));

            return $length >= $this->minLength && $length <= $this->maxLength;
        } catch (Exception $e) {
            // Silent error and log it
            Log::error($e);
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
        return __p('core::validation.name.length_between', [
            'min' => $this->minLength,
            'max' => $this->maxLength,
        ]);
    }
}
