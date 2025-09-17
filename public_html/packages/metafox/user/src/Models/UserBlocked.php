<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Database\Factories\UserBlockedFactory;

/**
 * Class UserBlocked.
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $owner_id
 * @property string $owner_type
 */
class UserBlocked extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE = 'user_blocked';

    protected $table = 'user_blocked';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
    ];

    /**
     * @return UserBlockedFactory
     */
    protected static function newFactory()
    {
        return UserBlockedFactory::new();
    }
}

// end
