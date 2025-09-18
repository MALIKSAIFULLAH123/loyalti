<?php

namespace MetaFox\Saved\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class SavedListItemView.
 *
 * @property int $id
 * @property int $list_id
 * @property int $user_id
 * @property int $saved_id
 */
class SavedListItemView extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'save_list_item_views';

    protected $table = 'save_list_item_views';

    public $incrementing = false;
    public $timestamps   = false;
    /** @var string[] */
    protected $fillable = [
        'list_id',
        'user_id',
        'saved_id',
    ];
}

// end
