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
 * Class ModeratorAccess.
 *
 * @property int $id
 */
class ModeratorAccess extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'forum_moderator_has_access';

    protected $table = 'forum_moderator_has_access';

    /** @var string[] */
    protected $fillable = [
        'moderator_id',
        'permission_name',
    ];

    public $timestamps = false;
}

// end
