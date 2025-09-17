<?php

namespace MetaFox\Forum\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ForumThreadTagData extends Pivot
{
    /**
     * @var array
     */
    protected $fillable = [
        'item_id',
        'tag_id',
    ];

    protected $table = 'forum_thread_tag_data';

    public $timestamps = false;
}
