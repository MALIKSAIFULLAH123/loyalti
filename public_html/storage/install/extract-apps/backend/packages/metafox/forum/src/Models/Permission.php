<?php

namespace MetaFox\Forum\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Forum\Database\Factories\PermissionFactory;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Permission
 *
 * @property int $id
 * @property string $name
 * @property string $var_name
 */
class Permission extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'forum_permission';

    protected $table = 'forum_permissions';

    /** @var string[] */
    protected $fillable = [
        'name',
        'var_name'
    ];

    public $timestamps = false;
}

// end
