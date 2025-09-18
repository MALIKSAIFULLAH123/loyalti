<?php

namespace MetaFox\Like\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Like\Database\Factories\LikeAggFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;

/**
 * Class LikeAgg.
 * @property int      $id
 * @property int      $item_id
 * @property string   $item_type
 * @property int      $reaction_id
 * @property int      $total_reaction
 * @property Reaction $reaction
 * @method   static   LikeAggFactory factory(...$parameters)
 */
class LikeAgg extends Model implements HasAmounts, Entity
{
    use HasEntity;
    use HasFactory;
    use HasItemMorph;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'like_aggregation';

    protected $table = 'like_aggregations';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'reaction_id',
    ];

    /**
     * @return LikeAggFactory
     */
    protected static function newFactory(): LikeAggFactory
    {
        return LikeAggFactory::new();
    }

    public function reaction(): BelongsTo
    {
        return $this->belongsTo(Reaction::class);
    }
}

// end
