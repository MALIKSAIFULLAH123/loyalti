<?php

namespace MetaFox\Ban\Rules;

use MetaFox\Ban\Supports\Constants;

class BanEmailRule extends BanRule
{
    protected function getType(): string
    {
        return Constants::BAN_EMAIL_TYPE;
    }

    protected function defineFailedMessage(): string
    {
        return __p('ban::validation.this_email_is_not_allowed_to_be_used');
    }

    protected function hasValidStructure(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
