<?php

namespace MetaFox\Advertise\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Advertise\Database\Factories\GenderFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Gender.
 *
 * @property        int           $id
 * @method   static GenderFactory factory(...$parameters)
 */
class Gender extends Pivot implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'advertise_gender';

    protected $table = 'advertise_genders';

    protected $foreignKey = 'item_id';

    protected $relatedKey = 'gender_id';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'gender_id',
    ];

    /**
     * @return GenderFactory
     */
    protected static function newFactory()
    {
        return GenderFactory::new();
    }
}

// end
