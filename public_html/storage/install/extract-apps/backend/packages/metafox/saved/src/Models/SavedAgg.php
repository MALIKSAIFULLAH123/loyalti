<?php

namespace MetaFox\Saved\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserAsOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Saved\Database\Factories\SavedAggFactory;

/**
 * Class SavedAgg.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property string $item_type
 * @property int    $total_saved
 * @method   static SavedAggFactory factory(...$parameters)
 */
class SavedAgg extends Model implements Entity, HasAmounts
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasUserAsOwnerMorph;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'saved_aggregation';

    protected $table = 'saved_aggregations';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'item_type',
        'total_saved',
    ];

    /**
     * @return SavedAggFactory
     */
    protected static function newFactory(): SavedAggFactory
    {
        return SavedAggFactory::new();
    }
}
