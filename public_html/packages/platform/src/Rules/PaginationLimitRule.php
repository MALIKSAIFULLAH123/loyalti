<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class PaginationLimitRule.
 */
class PaginationLimitRule implements Rule
{
    private int $min = Pagination::DEFAULT_MIN_ITEM_PER_PAGE;
    private int $max = Pagination::DEFAULT_MAX_ITEM_PER_PAGE;

    public function __construct(?int $min = null, ?int $max = null)
    {
        if ($min != null) {
            $this->min = $min;
        }

        if ($max != null) {
            $this->max = $max;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if ($value < $this->min) {
            return false;
        }

        if ($value > $this->max) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array<string, mixed>|null
     */
    public function message()
    {
        return __('validation.between.numeric', [
            'min' => $this->min,
            'max' => $this->max,
        ]);
    }
}
