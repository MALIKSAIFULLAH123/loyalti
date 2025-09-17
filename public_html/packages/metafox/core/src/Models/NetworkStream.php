<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NetworkStream.
 * @property int $network_id
 * @property int $item_id
 * @property int $item_type
 */
class NetworkStream extends Model
{
    protected $table = 'core_network_streams';

    protected $primaryKey = 'stream_id';

    public $timestamps = false;

    protected $fillable = [
        'network_id',
        'item_id',
        'item_type',
    ];
}
