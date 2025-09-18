<?php

namespace MetaFox\Subscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Subscription\Database\Factories\SubscriptionComparisonDataFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class SubscriptionComparisonData.
 *
 * @property int    $id
 * @method   static SubscriptionComparisonDataFactory factory(...$parameters)
 */
class SubscriptionComparisonData extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'subscription_comparison_data';

    protected $table = 'subscription_comparisons_data';

    /** @var string[] */
    protected $fillable = [
        'comparison_id',
        'package_id',
        'type',
        'value',
    ];

    /**
     * @return SubscriptionComparisonDataFactory
     */
    protected static function newFactory()
    {
        return SubscriptionComparisonDataFactory::new();
    }
}
