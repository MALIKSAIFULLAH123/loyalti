<?php

namespace MetaFox\Saved\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;

/**
 * Class SavedListData.
 *
 * @property int       $id
 * @property int       $list_id
 * @property int       $saved_id
 * @property Saved     $savedItems
 * @property SavedList $savedLists
 */
class SavedListData extends Pivot
{
    use HasEntity;
    use HasItemMorph;

    public const ENTITY_TYPE = 'saved_list_data';

    protected $table = 'saved_list_data';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'list_id',
        'saved_id',
    ];

    public function savedItems(): BelongsTo
    {
        return $this->belongsTo(Saved::class, 'saved_id', 'id');
    }

    public function savedLists(): BelongsTo
    {
        return $this->belongsTo(SavedList::class, 'list_id', 'id');
    }
}
