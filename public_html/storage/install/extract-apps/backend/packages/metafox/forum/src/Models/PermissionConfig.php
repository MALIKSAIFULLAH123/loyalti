<?php

namespace MetaFox\Forum\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Forum\Database\Factories\PermissionConfigFactory;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class PermissionConfig
 *
 * @property int $id
 * @method static PermissionConfigFactory factory(...$parameters)
 */
class PermissionConfig extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'forum_permission_config';

    protected $table = 'forum_permission_configs';

    /** @var string[] */
    protected $fillable = [
        'forum_id',
        'permission_name',
    ];

    public $timestamps = false;

    /**
     * @return PermissionConfigFactory
     */
    protected static function newFactory()
    {
        return PermissionConfigFactory::new();
    }
}

// end
