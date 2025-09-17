<?php

namespace MetaFox\Profile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class OptionData.
 *
 * @property int    $id
 * @property int    $custom_option_id
 * @property int    $item_id
 * @property Option $option
 *
 * @mixin Builder
 */
class OptionData extends Pivot implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'user_custom_option_data';

    public $timestamps = false;

    protected $table = 'user_custom_option_data';

    protected $foreignKey = 'item_id';

    protected $relatedKey = 'custom_option_id';

    protected $fillable = [
        'custom_option_id',
        'item_id',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'custom_option_id', 'id');
    }
}
