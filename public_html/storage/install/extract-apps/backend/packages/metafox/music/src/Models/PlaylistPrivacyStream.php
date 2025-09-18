<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class  PlaylistPrivacyStream.
 */
class PlaylistPrivacyStream extends Model
{
    use HasEntity;

    public const ENTITY_TYPE = 'music_playlist_privacy_stream';

    protected $table = 'music_playlist_privacy_streams';

    public $timestamps = false;

    protected $fillable = [
        'privacy_id',
        'item_id',
    ];
}
