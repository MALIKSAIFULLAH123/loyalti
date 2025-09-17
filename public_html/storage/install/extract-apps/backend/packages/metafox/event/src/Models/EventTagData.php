<?php

namespace MetaFox\Event\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class EventTagData.
 * @mixin Builder
 * @property int    $id
 * @property int    $item_id
 * @property int    $tag_id
 * @property string $tag_text
 */
class EventTagData extends Pivot
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'event_tag_data';

    /**
     * @var string[]
     */
    protected $fillable = [
        'item_id',
        'tag_id',
    ];
}
