<?php

namespace MetaFox\Friend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Database\Factories\FriendTagBlockedFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class FriendTagBlocked
 *
 * @property int $id
 * @method static FriendTagBlockedFactory factory(...$parameters)
 */
class FriendTagBlocked extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'friend_tag_blocked';

    protected $table = 'friend_tag_blocked';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'item_id',
        'item_type',
    ];

    /**
     * @return FriendTagBlockedFactory
     */
    protected static function newFactory()
    {
        return FriendTagBlockedFactory::new();
    }
}

// end
