<?php

namespace MetaFox\Saved\Models;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

class SavedSearchItem extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'saved_search_item';

    protected $table = 'saved_search_items';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'item_type',
        'title',
    ];
}
