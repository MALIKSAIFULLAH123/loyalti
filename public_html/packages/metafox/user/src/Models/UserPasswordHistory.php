<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserPassword.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property string $password
 */
class UserPasswordHistory extends Model
{
    protected $table = 'user_password_histories';

    protected $fillable = [
        'user_id',
        'user_type',
        'password',
    ];
}
