<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class SongTagData
 *
 * @property int $id
 */
class SongTagData extends Pivot
{
    public $timestamps = false;

    protected $table = 'music_song_tag_data';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'tag_id',
    ];
}

// end
