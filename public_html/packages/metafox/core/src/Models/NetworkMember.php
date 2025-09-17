<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NetworkMember.
 * @property int $user_id
 * @property int $network_id
 */
class NetworkMember extends Model
{
    protected $table = 'core_network_members';

    public $timestamps = false;

    protected $fillable = [
        'network_id',
        'user_id',
    ];
}
