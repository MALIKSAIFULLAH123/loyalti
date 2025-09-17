<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Network.
 *
 * @property int    $network_id
 * @property int    $parent_network_id
 * @property int    $item_id
 * @property string $item_type
 */
class Network extends Model
{
    protected $table = 'core_networks';

    public $timestamps = false;

    protected $fillable = [
        'parent_network_id',
        'item_id',
        'item_type',
    ];
}
