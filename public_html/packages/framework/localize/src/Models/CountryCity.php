<?php

namespace MetaFox\Localize\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * @property int          $id
 * @property string       $state_code
 * @property string       $city_code
 * @property string       $fips_code
 * @property string       $post_codes
 * @property string       $name
 * @property int          $ordering
 * @property string       $capital
 * @property int          $population
 * @property float        $longitude
 * @property float        $latitude
 * @property CountryChild $countryChild
 */
class CountryCity extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'country_city';

    protected $table = 'core_country_cities';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'state_code',
        'city_code',
        'fips_code',
        'post_codes',
        'longitude',
        'latitude',
        'capital',
        'population',
        'ordering',
    ];

    public function countryChild(): BelongsTo
    {
        return $this->belongsTo(CountryChild::class, 'state_code', 'state_code');
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        $countryChild = $this->countryChild;
        if (!$countryChild) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        $country = $countryChild->country;
        if (!$country) {
            return MetaFoxConstant::EMPTY_STRING;
        }
        
        return url_utility()->makeApiUrl(sprintf('localize/country/%s/state/%s/city/browse', $country->entityId(), $countryChild->entityId()));
    }
}
