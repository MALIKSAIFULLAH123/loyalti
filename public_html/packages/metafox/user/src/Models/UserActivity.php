<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Database\Factories\UserActivityFactory;

/**
 * Class UserActivity.
 *
 * @property int    $id
 * @property string $last_login
 * @property string $last_activity
 * @property string $last_ip_address
 * @method   static UserActivityFactory factory(...$parameters)
 */
class UserActivity extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_activity';

    protected $table = 'user_activities';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'last_login',
        'last_activity',
        'last_ip_address',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_activity' => 'datetime',
        'last_login'       => 'datetime',
    ];

    /**
     * @return UserActivityFactory
     */
    protected static function newFactory(): UserActivityFactory
    {
        return UserActivityFactory::new();
    }
}

// end
