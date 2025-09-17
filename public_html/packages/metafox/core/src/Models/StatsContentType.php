<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class StatsContentType.
 *
 * @property int          $id
 * @property string       $name
 * @property string|null  $icon
 * @property string|null  $to
 * @property int          $ordering
 * @property string       $created_at
 * @property string       $updated_at
 * @property StatsContent $latestStatistic
 */
class StatsContentType extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'stats_content_type';

    protected $table = 'stats_content_types';

    /** @var string[] */
    protected $fillable = [
        'name',
        'icon',
        'to',
        'operation',
        'ordering',
        'created_at',
        'updated_at',
    ];

    public function getAdminBrowseUrlAttribute(): string
    {
        return '';
    }

    public function getAdminEditUrlAttribute(): string
    {
        return '';
    }

    public function latestStatistic(): HasOne
    {
        return $this->hasOne(StatsContent::class, 'name', 'name')->whereNull('period')->where('group', '*')->limit(1);
    }
}

// end
