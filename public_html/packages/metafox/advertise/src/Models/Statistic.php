<?php

namespace MetaFox\Advertise\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Advertise\Database\Factories\StatisticFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Statistic.
 *
 * @property        int              $id
 * @method   static StatisticFactory factory(...$parameters)
 */
class Statistic extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'advertise_statistic';

    protected $table = 'advertise_statistics';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'total_impression',
        'total_click',
    ];

    public $timestamps = false;

    /**
     * @return StatisticFactory
     */
    protected static function newFactory()
    {
        return StatisticFactory::new();
    }
}

// end
