<?php

namespace MetaFox\ActivityPoint\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Database\Factories\PointSettingFactory;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class PointSetting.
 *
 * @mixin Builder
 *
 * @property int    $id
 * @property string $name
 * @property string $action
 * @property string $module_id
 * @property string $package_id
 * @property string $description_phrase
 * @property string $description
 * @property bool   $is_active
 * @property int    $points
 * @property int    $max_earned
 * @property int    $period
 * @property string $created_at
 * @property string $updated_at
 * @property ?Role  $role
 * @property ?array $extra
 * @property array  $disabledFields
 *
 * @method static PointSettingFactory factory(...$parameters)
 */
class PointSetting extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'activitypoint_setting';

    protected $table = 'apt_settings';

    public const POINT_SETTING_ACTIONS = ['create'];

    /**
     * @var string[]
     */
    protected $appends = ['description'];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_active' => 'boolean',
        'extra'     => 'array',
    ];

    /** @var string[] */
    protected $fillable = [
        'name',
        'role_id',
        'action',
        'module_id',
        'package_id',
        'description_phrase',
        'is_active',
        'points',
        'max_earned',
        'period',
        'created_at',
        'updated_at',
        'extra',
    ];

    protected static function newFactory(): PointSettingFactory
    {
        return PointSettingFactory::new();
    }

    public function getDescriptionAttribute(): string
    {
        return __p($this->description_phrase);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class, 'point_setting_id', 'id');
    }

    /**
     * @return array<string>
     */
    public function getDisabledFieldsAttribute(): array
    {
        if (!is_array($this->extra)) {
            return [];
        }

        return Arr::get($this->extra, 'disabled', []);
    }
}

// end
