<?php

namespace MetaFox\Forum\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class UserRolePermission.
 *
 * @property int $id
 */
class UserRolePermission extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'forum_user_role_permission';

    protected $table = 'forum_user_role_permissions';

    /** @var string[] */
    protected $fillable = [
        'forum_id',
        'role_id',
        'permission_name',
        'permission_value',
    ];

    public $timestamps = false;
}

// end
