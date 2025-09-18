<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class AlbumTagData
 *
 * @property int $id
 */
class AlbumTagData extends Pivot
{
    public $timestamps = false;

    protected $table = 'music_album_tag_data';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'tag_id',
    ];
}

// end
