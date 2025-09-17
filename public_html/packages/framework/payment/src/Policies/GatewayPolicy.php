<?php

namespace MetaFox\Payment\Policies;

use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Policies\Contracts\GatewayPolicyInterface;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class GatewayPolicy.
 * @ignore
 * @codeCoverageIgnore
 */
class GatewayPolicy implements GatewayPolicyInterface
{
    use HasPolicyTrait;

    protected string $type = Gateway::class;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }
}
