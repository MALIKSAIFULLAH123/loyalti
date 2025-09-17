<?php

namespace MetaFox\User\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Interface PlatformPermission.
 */
interface PlatformPermission
{
    /**
     * A permission can be applied to roles.
     */
    public function rolesHasValuePermissions(): BelongsToMany;

    /**
     * Assign the given role to the model.
     *
     * @param array<string, mixed> $params
     *
     * @return $this
     */
    public function assignRoleWithPivot(array $params): self;
}
