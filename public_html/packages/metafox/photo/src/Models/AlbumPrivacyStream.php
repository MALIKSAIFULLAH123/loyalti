<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Model;

class AlbumPrivacyStream extends Model
{
    protected $table = 'photo_album_privacy_streams';

    public $timestamps = false;

    protected $fillable = [
        'privacy_id',
        'item_id',
    ];
}

// end
