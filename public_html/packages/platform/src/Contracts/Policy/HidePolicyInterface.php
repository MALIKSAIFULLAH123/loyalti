<?php

namespace MetaFox\Platform\Contracts\Policy;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User as User;

/**
 * Interface HidePolicyInterface
 * @package MetaFox\Platform\Contracts\Policy
 */
interface HidePolicyInterface
{
    public function hide(User $user, ?Content $resource = null): bool;
}
