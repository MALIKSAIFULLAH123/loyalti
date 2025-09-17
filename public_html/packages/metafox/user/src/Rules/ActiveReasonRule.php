<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\User\Models\CancelReason;

/**
 * Class ActiveReasonRule.
 */
class ActiveReasonRule implements Rule
{
    protected bool $isActive = false;

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
        $result = null;
        try {
            // fixed security risk: sql injection
            $result = CancelReason::query()->find(intval($value, 10));
        } catch (ModelNotFoundException) {
        }

        if ($result == null) {
            return false;
        }

        $this->isActive = $result->is_active;

        return $result->is_active;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        if (!$this->isActive) {
            return __p('user::validation.reason_id.active');
        }

        return __p('user::validation.reason_id.exists');
    }
}
