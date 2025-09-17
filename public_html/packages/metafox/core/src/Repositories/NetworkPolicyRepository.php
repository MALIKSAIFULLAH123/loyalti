<?php
/**
 * @author developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Repositories;

use MetaFox\Platform\Contracts\NetworkPolicy;
use MetaFox\Platform\Contracts\User;

class NetworkPolicyRepository implements NetworkPolicy
{
    public function checkNetworkPermission(?User $user, int $network, string $permission)
    {
        return true;
    }

    public function hasNetworkRole(?User $user, int $network, string $role)
    {
        // TODO: Implement hasNetworkRole() method.
    }
}
