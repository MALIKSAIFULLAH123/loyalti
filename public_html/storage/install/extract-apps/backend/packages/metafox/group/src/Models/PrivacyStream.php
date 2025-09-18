<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyStream extends Model
{
    protected $table = 'group_privacy_streams';

    public $timestamps = false;

    protected $fillable = [
        'privacy_id',
        'item_id',
    ];
}

// end
