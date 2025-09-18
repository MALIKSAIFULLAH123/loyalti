<?php

namespace MetaFox\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyStream extends Model
{
    protected $table = 'marketplace_listing_privacy_streams';

    public $timestamps = false;

    protected $fillable = [
        'privacy_id',
        'item_id',
    ];
}
