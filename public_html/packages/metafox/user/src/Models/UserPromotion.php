<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Database\Factories\UserPromotionFactory;

/**
 * Class UserPromotion.
 *
 * @property int                  $id
 * @method   UserPromotionFactory factory(...$parameters)
 */
class UserPromotion extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_promotion';

    protected $table = 'user_promotion';

    /** @var string[] */
    protected $fillable = [
        'is_active',
        'user_group_id',
        'upgrade_user_group_id',
        'total_activity',
        'total_day',
        'updated_at',
        'created_at',
    ];

    /**
     * @return UserPromotionFactory
     */
    protected static function newFactory()
    {
        return UserPromotionFactory::new();
    }
}

// end
