<?php

namespace MetaFox\Localize\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Localize\Database\Factories\CountryChildFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class CountryChild.
 *
 * @mixin Builder
 *
 * @property        int                      $id
 * @property        string                   $country_iso
 * @property        string                   $state_iso
 * @property        int                      $state_code
 * @property        int                      $geonames_code
 * @property        string                   $fips_code
 * @property        string                   $post_codes
 * @property        string                   $name
 * @property        string                   $timezone
 * @property        int                      $ordering
 * @property        Country                  $country
 * @property        Collection|CountryCity[] $cities
 * @method   static CountryChildFactory      factory($count = null, $state = [])
 */
class CountryChild extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'country_state';

    protected $table = 'core_country_states';

    protected $fillable = [
        'country_iso',
        'state_iso',
        'state_code',
        'geonames_code',
        'fips_code',
        'post_codes',
        'name',
        'timezone',
        'ordering',
    ];

    public $timestamps = false;

    /** @var array<string, string> */
    protected $casts = [
        'post_codes' => 'array',
    ];

    protected static function newFactory(): CountryChildFactory
    {
        return CountryChildFactory::new();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_iso', 'country_iso');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(CountryCity::class, 'state_code', 'state_code');
    }
}
