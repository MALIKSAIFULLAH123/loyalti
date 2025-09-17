<?php

namespace MetaFox\Mfa\Policies;

use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\Platform\Contracts\User;

/**
 * Class ServicePolicy.
 * @ignore
 * @codeCoverageIgnore
 */
class ServicePolicy
{
    public function view(User $user): bool
    {
        return true;
    }
}
