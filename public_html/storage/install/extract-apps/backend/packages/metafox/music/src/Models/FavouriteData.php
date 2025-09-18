<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class FavouriteData.
 *
 * @property int $user_id
 * @property int $item_id
 */
class FavouriteData extends Pivot
{
    use HasEntity;

    public const ENTITY_TYPE = 'music_favourite_data';

    protected $table = 'music_favourite_data';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'item_id',
        'item_type',
        'created_at',
        'updated_at',
    ];
}
