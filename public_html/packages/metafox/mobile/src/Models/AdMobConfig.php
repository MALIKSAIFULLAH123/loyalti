<?php

namespace MetaFox\Mobile\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Mobile\Database\Factories\AdMobConfigFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Mobile\Models\AdMobPage as Page;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Mobile\Models\AdMobConfigRoleData;

/**
 * Class AdMobConfig.
 *
 * @property        int                $id
 * @property        int                $user_id
 * @property        string             $user_type
 * @property        string             $name
 * @property        string             $type
 * @property        string             $type_name
 * @property        string             $frequency_capping
 * @property        string             $frequency_capping_title
 * @property        int                $view_capping
 * @property        int                $time_capping_impression
 * @property        string             $time_capping_frequency
 * @property        array              $location_priority
 * @property        bool               $is_sticky
 * @property        bool               $is_active
 * @property        string             $created_at
 * @property        string             $updated_at
 * @property        Collection<Page>   $pages
 * @property        Collection<Role>   $roles
 * @method   static AdMobConfigFactory factory(...$parameters)
 */
class AdMobConfig extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasNestedAttributes;

    public const ENTITY_TYPE = 'ad_mob_config';

    // Types
    public const AD_MOB_TYPE_BANNER       = 'banner';
    public const AD_MOB_TYPE_INTERSTITIAL = 'interstitial';
    public const AD_MOB_TYPE_REWARDED     = 'rewarded';

    // Frequency Capping
    public const AD_MOB_FREQUENCY_TIMES  = 'times';
    public const AD_MOB_FREQUENCY_VIEWS  = 'views';
    public const AD_MOB_FREQUENCY_RANDOM = 'rand';
    public const AD_MOB_FREQUENCY_NONE   = 'none';

    //TIME FREQUENCY
    public const AD_MOB_FREQUENCY_PER_MINUTE = 'per_minute';
    public const AD_MOB_FREQUENCY_PER_HOUR   = 'per_hour';
    public const AD_MOB_FREQUENCY_PER_DAY    = 'per_day';

    //AD MOB LOCATION
    public const AD_MOB_LOCATION_TOP    = 'top';
    public const AD_MOB_LOCATION_BOTTOM = 'bottom';

    public const AD_MOB_TYPE = [
        self::AD_MOB_TYPE_BANNER       => 'mobile::phrase.ad_mob_type_banner',
        self::AD_MOB_TYPE_INTERSTITIAL => 'mobile::phrase.ad_mob_type_interstitial',
        self::AD_MOB_TYPE_REWARDED     => 'mobile::phrase.ad_mob_type_rewarded',
    ];

    public const AD_MOB_FREQUENCY = [
        self::AD_MOB_FREQUENCY_NONE   => 'mobile::phrase.no_frequency_capping',
        self::AD_MOB_FREQUENCY_TIMES  => 'mobile::phrase.frequency_times',
        self::AD_MOB_FREQUENCY_VIEWS  => 'mobile::phrase.frequency_views',
        self::AD_MOB_FREQUENCY_RANDOM => 'mobile::phrase.frequency_random',
    ];

    public const AD_MOB_TIME_FREQUENCY = [
        self::AD_MOB_FREQUENCY_PER_MINUTE => 'mobile::phrase.frequency_times_per_minute',
        self::AD_MOB_FREQUENCY_PER_HOUR   => 'mobile::phrase.frequency_times_per_hour',
        self::AD_MOB_FREQUENCY_PER_DAY    => 'mobile::phrase.frequency_times_per_day',
    ];

    public const AD_MOB_LOCATIONS = [
        self::AD_MOB_LOCATION_TOP    => 'mobile::phrase.location_top',
        self::AD_MOB_LOCATION_BOTTOM => 'mobile::phrase.location_bottom',
    ];

    protected $table = 'ad_mob_configs';

    protected $casts = [
        'location_priority' => 'array',
        'is_sticky'         => 'boolean',
        'is_active'         => 'boolean',
    ];

    protected $appends = [
        'type_name',
        'frequency_capping_title',
    ];

    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'roles',
    ];

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'name',
        'type',
        'frequency_capping',
        'view_capping',
        'time_capping_impression',
        'time_capping_frequency',
        'location_priority',
        'is_sticky',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function pages(): belongsToMany
    {
        return $this->belongsToMany(
            AdMobPage::class,
            'ad_mob_config_page_data',
            'config_id',
            'page_id'
        )->using(AdMobConfigPageData::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'ad_mob_config_role_data',
            'config_id',
            'role_id'
        )->using(AdMobConfigRoleData::class);
    }

    /**
     * @return AdMobConfigFactory
     */
    protected static function newFactory()
    {
        return AdMobConfigFactory::new();
    }

    public function getAdminEditUrlAttribute(): string
    {
        return sprintf('/mobile/admob/edit/' . $this->entityId());
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return sprintf('/mobile/admob/browse');
    }

    public function getTypeNameAttribute(): string
    {
        $key = Arr::get(self::AD_MOB_TYPE, $this->type, '');

        return __p($key);
    }

    public function getFrequencyCappingTitleAttribute(): string
    {
        if (!$this->frequency_capping) {
            return __p('mobile::phrase.no_frequency_capping');
        }

        $key = Arr::get(self::AD_MOB_FREQUENCY, $this->frequency_capping);

        return $key ? __($key) : __p('mobile::phrase.no_frequency_capping');
    }
}

// end
