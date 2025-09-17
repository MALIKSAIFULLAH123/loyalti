<?php

namespace MetaFox\Chat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFox\Chat\Database\Factories\RoomFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Room.
 *
 * @property        int         $id
 * @method   static RoomFactory factory(...$parameters)
 */
class Room extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'chat_room';

    protected $table = 'chat_rooms';

    public const ROOM_DELETED        = 'room_delete';
    public const ROOM_UPDATED        = 'room_update';
    public const ROOM_USER_BLOCKED   = 'room_user_blocked';
    public const ROOM_USER_UNBLOCKED = 'room_user_unblocked';
    public const ROOM_USER_DELETED   = 'room_user_deleted';

    /** @var string[] */
    protected $fillable = [
        'name',
        'type',
        'uid',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'is_archived',
        'is_readonly',
        'updated_at',
        'created_at',
    ];

    /**
     * @return RoomFactory
     */
    protected static function newFactory()
    {
        return RoomFactory::new();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'room_id', 'id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'room_id', 'id');
    }
}

// end
