<?php

namespace MetaFox\Authorization\Models\Pivot;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int   $permission_id
 * @property int   $role_id
 * @property mixed $value
 */
class RoleValuePermission extends Pivot
{
    protected $table = 'auth_role_has_value_permissions';

    /**
     * @var string[]
     */
    protected $casts = [
        'value' => 'array',
    ];
}
