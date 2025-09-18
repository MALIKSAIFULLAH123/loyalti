<?php

namespace MetaFox\LiveStreaming\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * stub: /packages/models/model_privacy_stream.stub.
 */

/**
 * Class LiveVideoPrivacyStream.
 *
 * @property int $id
 */
class LiveVideoPrivacyStream extends Model
{
    protected $table = 'livestreaming_privacy_streams';

    public $timestamps = false;

    protected $fillable = [
        'privacy_id',
        'item_id',
    ];
}

// end
