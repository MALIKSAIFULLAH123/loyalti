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
 * Class CountryData
 *
 * @property int $id
 */
class CountryData extends Pivot implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'announcement_country_data';

    protected $table = 'announcement_country_data';

    protected $foreignKey = 'item_id';

    protected $relatedKey = 'country_iso';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'country_iso',
    ];

}

// end
