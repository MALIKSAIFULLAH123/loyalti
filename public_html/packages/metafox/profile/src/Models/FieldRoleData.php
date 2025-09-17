<?php

namespace MetaFox\Profile\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class FieldRoleData
 *
 * @property int $id
 * @property int $field_id
 * @property int $role_id
 */
class FieldRoleData extends Pivot implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'user_custom_field_role_data';

    protected $table = 'user_custom_field_role_data';

    public $timestamps = false;
    /** @var string[] */
    protected $fillable = [
        'field_id',
        'role_id',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class, 'field_id', 'id');
    }
}

// end
