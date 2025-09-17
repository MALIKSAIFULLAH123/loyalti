<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\User\Database\Factories\UserBanFactory;

/**
 * Class UserBan.
 *
 * @property int    $id
 * @property int    $start_time_stamp
 * @property int    $end_time_stamp
 * @property int    $return_user_group
 * @property string $reason
 * @property string $created_at
 * @property string $updated_at
 */
class UserBan extends Model
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE = 'user_ban';

    protected $table = 'user_ban';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'ban_id',
        'owner_id',
        'owner_type',
        'start_time_stamp',
        'end_time_stamp',
        'return_user_group',
        'reason',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return UserBanFactory
     */
    protected static function newFactory()
    {
        return UserBanFactory::new();
    }
}

// end
