<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * @property int                   $id
 * @property string                $name
 * @property string                $label
 * @property string                $value
 * @property string|null           $period
 * @property string                $group
 * @property string                $package_id
 * @property string                $created_at
 * @property StatsContentType|null $type
 *
 * @mixin Builder
 */
class StatsContent extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE             = 'stat_content';
    public const STAT_PERIOD_FIVE_MINUTE = '5m';
    public const STAT_PERIOD_ONE_HOUR    = '1h';
    public const STAT_PERIOD_ONE_DAY     = '1d';
    public const STAT_PERIOD_ONE_WEEK    = '1w';
    public const STAT_PERIOD_ONE_MONTH   = '1M';
    public const STAT_PERIOD_ONE_YEAR    = '1y';

    protected $table = 'core_stats_contents';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'label',
        'value',
        'package_id',
        'module_id',
        'period',
        'created_at',
        'group',
    ];

    public function getLabelAttribute(string $value): string
    {
        return __p($value);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(StatsContentType::class, 'name', 'name');
    }
}
