<?php

namespace MetaFox\Group\Support;

use MetaFox\Group\Contracts\RuleSupportContract;

class RuleSupport implements RuleSupportContract
{
    public function getDescriptionMaxLength(): int
    {
        return 500;
    }

    public function getDescriptionMinLength(): int
    {
        return 1;
    }
}
