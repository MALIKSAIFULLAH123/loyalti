<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Query\Builder;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class AdminAccess.
 *
 * @mixin Builder
 *
 * @property int    $id
 * @property string $ip_address
 * @property string $created_at
 * @property string $updated_at
 */
class AdminAccess extends Model implements Entity
{
    use HasEntity;
    use HasUserMorph;

    public const ENTITY_TYPE = 'admincp_access';

    public const USER_ACTIVE_LIMIT_IN_MINUTES = 5;

    protected $table = 'core_admincp_accesses';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'ip_address',
        'created_at',
        'updated_at',
    ];
}

// end
