<?php

namespace MetaFox\LiveStreaming\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * stub: /packages/models/model_network_stream.stub.
 */

/**
 * Class LiveVideoNetworkStream.
 *
 * @property int $id
 */
class LiveVideoNetworkStream extends Model
{
    protected $table = 'livestreaming_network_streams';

    public $timestamps = false;

    protected $fillable = [
        'privacy_id',
        'item_id',
    ];
}

// end
