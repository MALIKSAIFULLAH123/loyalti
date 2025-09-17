<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Model;

class UserHasRoles extends Model
{
    public $incrementing = false;

    public $timestamps =  false;

    protected $fillable = [
        'role_id',
        'model_id',
        'model_type',
    ];

    public function getTable()
    {
        return 'auth_model_has_roles';
    }
}