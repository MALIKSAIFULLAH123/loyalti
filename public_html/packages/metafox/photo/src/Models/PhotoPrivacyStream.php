<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoPrivacyStream extends Model
{
    protected $table = 'photo_privacy_streams';

    public $timestamps = false;

    protected $fillable = [
        'privacy_id',
        'item_id',
    ];
}

// end
