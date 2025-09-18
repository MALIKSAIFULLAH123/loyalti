<?php

namespace MetaFox\Poll\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyStream extends Model
{
    protected $table = 'poll_privacy_streams';

    public $timestamps = false;

    protected $fillable = [
        'privacy_id',
        'item_id',
    ];
}

// end
