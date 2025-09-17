<?php

namespace MetaFox\Subscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Subscription\Database\Factories\SubscriptionComparisonFactory;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class SubscriptionComparison.
 *
 * @property int $id
 * @method   static SubscriptionComparisonFactory factory(...$parameters)
 */
class SubscriptionComparison extends Model implements
    Entity,
    HasTitle
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'subscription_comparison';

    protected $table = 'subscription_comparisons';

    /** @var string[] */
    protected $fillable = [
        'title',
        'ordering',
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPackage::class, 'subscription_comparisons_data', 'comparison_id', 'package_id')
            ->join('subscription_comparisons', function (JoinClause $joinClause) {
                $joinClause->on('subscription_comparisons.id', '=', 'subscription_comparisons_data.comparison_id');
            })
            ->select(['subscription_comparisons.*', 'subscription_comparisons_data.type', 'subscription_comparisons_data.value', 'subscription_packages.title as package_title']);
    }

    /**
     * @return SubscriptionComparisonFactory
     */
    protected static function newFactory()
    {
        return SubscriptionComparisonFactory::new();
    }

    public function toTitle(): string
    {
        return Helper::handleTitleForView($this->title);
    }
}
