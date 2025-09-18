<?php

namespace MetaFox\Page\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Activity
 *
 * @property int    $id
 * @property int    $page_id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $item_id
 * @property string $item_type
 */
class Activity extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'page_activity';

    protected $table = 'page_activities';

    /** @var string[] */
    protected $fillable = [
        'page_id',
        'user_id',
        'user_type',
        'item_id',
        'item_type',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id', 'id');
    }
}

// end
