<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

interface NetworkPolicy
{
    public function checkNetworkPermission(?User $user, int $network, string $permission);

    public function hasNetworkRole(?User $user, int $network, string $role);
}
