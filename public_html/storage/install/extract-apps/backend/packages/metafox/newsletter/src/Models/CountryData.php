<?php

namespace MetaFox\Newsletter\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class CountryData.
 *
 * @property int $id
 */
class CountryData extends Pivot implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'newsletter_country_data';

    protected $table = 'newsletter_country_data';

    protected $foreignKey = 'newsletter_id';

    protected $relatedKey = 'country_iso';

    /** @var string[] */
    protected $fillable = [
        'newsletter_id',
        'country_iso',
    ];
}

// end
