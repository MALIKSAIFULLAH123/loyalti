<?php

namespace MetaFox\Localize\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Database\Factories\TimeZoneFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Timezone.
 *
 * @property int             $id
 * @property string          $code
 * @property string          $name
 * @property string          $offset
 * @property string          $diff_from_gtm
 * @property int             $is_active
 * @method   TimeZoneFactory factory(...$parameters)
 */
class Timezone extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public $timestamps = false;

    public const ENTITY_TYPE = 'timezone';
    public const IS_ACTIVE   = 1;

    protected $table = 'core_timezones';

    /** @var string[] */
    protected $fillable = [
        'code',
        'name',
        'offset',
        'diff_from_gtm',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return TimeZoneFactory
     */
    protected static function newFactory(): TimeZoneFactory
    {
        return TimeZoneFactory::new();
    }
}
