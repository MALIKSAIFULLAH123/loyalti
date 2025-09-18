<?php

namespace MetaFox\GettingStarted\Models;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Models\User;

class UserFirstLogin extends Model implements
    Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'user_first_login';

    protected $table = 'gettingstarted_user_first_login';

    protected $fillable = [
        'user_id',
        'user_type',
        'resolution',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
