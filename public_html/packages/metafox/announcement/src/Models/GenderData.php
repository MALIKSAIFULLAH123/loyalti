<?php

namespace MetaFox\Announcement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class GenderData
 *
 * @property int $id
 */
class GenderData extends Pivot implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'announcement_gender_data';

    protected $table = 'announcement_gender_data';

    protected $foreignKey = 'item_id';

    protected $relatedKey = 'gender_id';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'gender_id',
    ];

}

// end
