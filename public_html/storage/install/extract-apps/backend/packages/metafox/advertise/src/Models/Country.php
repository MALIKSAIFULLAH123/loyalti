<?php

namespace MetaFox\Advertise\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Advertise\Database\Factories\CountryFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Country.
 *
 * @property        int            $id
 * @method   static CountryFactory factory(...$parameters)
 */
class Country extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'advertise_country';

    protected $table = 'advertise_countries';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'address',
        'city_code',
        'state_code',
        'latitude',
        'longitude',
        'country_code',
    ];

    /**
     * @return CountryFactory
     */
    protected static function newFactory()
    {
        return CountryFactory::new();
    }
}

// end
