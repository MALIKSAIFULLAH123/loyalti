<?php

namespace MetaFox\Localize\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Localize\Database\Factories\CountryFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Models\UserProfile;

/**
 * Class Country.
 *
 * @property int                       $id
 * @property string                    $country_iso
 * @property string                    $code
 * @property string                    $code3
 * @property string                    $numeric_code
 * @property int                       $geonames_code
 * @property string                    $fips_code
 * @property int                       $area
 * @property string                    $currency
 * @property string                    $phone_prefix
 * @property string                    $mobile_format
 * @property string                    $landline_format
 * @property string                    $trunk_prefix
 * @property int                       $population
 * @property string                    $continent
 * @property string                    $language
 * @property string                    $name
 * @property int                       $ordering
 * @property int                       $is_active
 * @property Collection|CountryChild[] $children
 * @property Collection|CountryChild[] $states
 */
class Country extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'country';

    protected $table = 'core_countries';

    protected $fillable = [
        'country_iso',
        'code',
        'code3',
        'numeric_code',
        'geonames_code',
        'fips_code',
        'area',
        'currency',
        'phone_prefix',
        'mobile_format',
        'landline_format',
        'trunk_prefix',
        'population',
        'continent',
        'language',
        'name',
        'ordering',
        'is_active',
    ];

    public $timestamps = false;

    /**
     * @return CountryFactory
     */
    protected static function newFactory()
    {
        return CountryFactory::new();
    }

    /**
     * @return HasMany
     * @deprecated
     */
    public function children(): HasMany
    {
        return $this->hasMany(CountryChild::class, 'country_iso', 'country_iso');
    }

    public function states(): HasMany
    {
        return $this->hasMany(CountryChild::class, 'country_iso', 'country_iso');
    }

    public function users(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'country_iso', 'country_iso');
    }

    public function getIsUsingAttribute(): bool
    {
        return $this->users()->exists();
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return url_utility()->makeApiUrl(sprintf('localize/country/%s/state/browse', $this->entityId()));
    }

    public function getAdminEditUrlAttribute(): string
    {
        return url_utility()->makeApiUrl(sprintf('localize/country/%s/edit', $this->entityId()));
    }
}
