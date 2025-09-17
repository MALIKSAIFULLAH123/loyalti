<?php

namespace MetaFox\Poll\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class PollTagData.
 * @mixin Builder
 * @property int    $id
 * @property int    $item_id
 * @property int    $tag_id
 * @property string $tag_text
 */
class PollTagData extends Pivot
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'poll_tag_data';

    /**
     * @var string[]
     */
    protected $fillable = [
        'item_id',
        'tag_id',
    ];
}
