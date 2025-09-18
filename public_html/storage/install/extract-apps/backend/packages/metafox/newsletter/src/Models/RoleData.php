<?php

namespace MetaFox\Newsletter\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class RoleData.
 *
 * @property int $id
 */
class RoleData extends Pivot implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'newsletter_role_data';

    protected $table = 'newsletter_role_data';

    protected $foreignKey = 'newsletter_id';

    protected $relatedKey = 'role_id';

    /** @var string[] */
    protected $fillable = [
        'newsletter_id',
        'role_id',
    ];
}

// end
