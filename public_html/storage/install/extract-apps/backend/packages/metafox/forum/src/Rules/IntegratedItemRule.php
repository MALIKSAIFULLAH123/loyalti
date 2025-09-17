<?php

namespace MetaFox\Forum\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;

/**
 * IntegratedItemRule.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class IntegratedItemRule implements Rule
{
    public function __construct(protected User $context)
    {
    }

    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        $context = $this->context;

        if ($context->hasPermissionTo('forum_thread.attach_poll')) {
            return true;
        }

        $id = Arr::get($value, 'id', 0);

        if ($id > 0) {
            return true;
        }

        return false;
    }

    /**
     * message.
     *
     * @return string
     */
    public function message(): string
    {
        return __p('forum::validation.can_not_attach_poll_due_to_privacy');
    }
}
