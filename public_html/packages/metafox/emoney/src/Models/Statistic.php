<?php

namespace MetaFox\EMoney\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\EMoney\Database\Factories\StatisticFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Statistic.
 *
 * @property        int              $id
 * @property        int              $user_id
 * @property        string           $user_type
 * @property        string           $currency
 * @property        float            $total_pending_transaction
 * @property        float            $total_balance
 * @property        float            $total_pending
 * @property        float            $total_earned
 * @property        float            $total_withdrawn
 * @property        float            $total_purchased
 * @property        float            $total_sent
 * @property        float            $total_reduced
 * @method   static StatisticFactory factory(...$parameters)
 */
class Statistic extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'ewallet_statistic';

    protected $table = 'emoney_statistics';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'currency',
        'total_balance',
        'total_pending',
        'total_earned',
        'total_withdrawn',
        'total_pending_transaction',
        'total_purchased',
        'total_sent',
        'total_reduced',
    ];

    public $casts = [
        'total_balance'             => 'float',
        'total_pending'             => 'float',
        'total_earned'              => 'float',
        'total_withdrawn'           => 'float',
        'total_pending_transaction' => 'float',
        'total_purchased'           => 'float',
        'total_reduced'             => 'float',
        'total_sent'                => 'float',
    ];

    /**
     * @return StatisticFactory
     */
    protected static function newFactory()
    {
        return StatisticFactory::new();
    }
}

// end
