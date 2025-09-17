<?php

namespace MetaFox\Platform\Contracts\Policy;

use MetaFox\Platform\Contracts\User;

/**
 * Interface ViewOnProfilePagePolicyInterface
 * @package MetaFox\Platform\Contracts\Policy
 * @deprecated
 */
interface ViewOnProfilePagePolicyInterface
{
    public function viewOnProfilePage(User $user, User $owner): bool;
}
