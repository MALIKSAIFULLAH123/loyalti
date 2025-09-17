<?php

namespace MetaFox\Profile\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class FieldVisibleRoleData
 *
 * @property int $id
 * @property int $field_id
 * @property int $role_id
 */
class FieldVisibleRoleData extends Pivot implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_custom_field_visible_role_data';

    protected $table = 'user_custom_field_visible_role_data';

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
