<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class SongPrivacyStream.
 */
class SongPrivacyStream extends Model
{
    use HasEntity;

    public const ENTITY_TYPE = 'music_song_privacy_stream';

    protected $table = 'music_song_privacy_streams';

    public $timestamps = false;

    protected $fillable = [
        'privacy_id',
        'item_id',
    ];
}
