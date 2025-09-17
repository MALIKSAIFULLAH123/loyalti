<?php

namespace MetaFox\Friend\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Friend\Models\FriendRequest;

/**
 * Class FriendRequestActionRule.
 * @ignore
 * @codeCoverageIgnore
 */
class FriendRequestActionRule implements Rule
{
    /**
     * @var array<int, string>
     */
    private array $allowAction = [
        FriendRequest::ACTION_APPROVE,
        FriendRequest::ACTION_DENY,
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (!in_array($value, $this->allowAction)) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return __p('validation.in_array', ['other' => implode(', ', $this->allowAction)]);
    }
}
