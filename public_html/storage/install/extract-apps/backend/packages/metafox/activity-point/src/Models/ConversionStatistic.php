<?php

namespace MetaFox\ActivityPoint\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class ConversionStatistic
 *
 * @property int $id
 * @property int $user_id
 * @property string $user_type
 * @property int $total_converted
 * @property int $total_pending
 */
class ConversionStatistic extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'activitypoint_conversion_statistic';

    protected $table = 'apt_conversion_statistics';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'total_converted',
        'total_pending',
    ];

    public $casts = [
        'total_converted' => 'integer',
        'total_pending'   => 'integer',
    ];

    public $timestamps = false;
}

// end
