<?php

namespace MetaFox\User\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MetaFox\Authorization\Models\Pivot\RoleValuePermission;

/**
 * Interface RoleHasValuePermission.
 * @property BelongsToMany|RoleValuePermission[] $valuePermissions
 */
interface RoleHasValuePermission
{
    public function valuePermissions(): BelongsToMany;
}
