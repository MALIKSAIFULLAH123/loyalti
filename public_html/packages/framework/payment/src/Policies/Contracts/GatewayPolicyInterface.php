<?php

namespace MetaFox\Payment\Policies\Contracts;

use MetaFox\Platform\Contracts\User as User;

/**
 * Interface GatewayPolicyInterface.
 *
 * @ignore
 * @codeCoverageIgnore
 */
interface GatewayPolicyInterface
{
    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool;

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool;

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user): bool;
}
