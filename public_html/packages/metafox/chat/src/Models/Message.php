<?php

namespace MetaFox\Chat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Chat\Database\Factories\MessageFactory;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Traits\HasTotalAttachmentTrait;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Message.
 *
 * @property int    $id
 * @property int    $room_id
 * @property int    $user_id
 * @property string $user_type
 * @property string $type
 * @property string $message
 * @property string $created_at
 * @property string $updated_at
 * @property string $extra
 *
 * @method static MessageFactory factory(...$parameters)
 */
class Message extends Model implements
    Entity,
    HasTotalAttachment
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasTotalAttachmentTrait;
    use HasContent;

    public const ENTITY_TYPE = 'chat_message';

    protected $table = 'chat_messages';

    public const MESSAGE_CREATE  = 'message_create';
    public const MESSAGE_UPDATE  = 'message_update';
    public const MESSAGE_REACT   = 'message_react';

    /** @var string[] */
    protected $fillable = [
        'room_id',
        'user_id',
        'user_type',
        'type',
        'message',
        'extra',
        'reactions',
        'total_attachment',
    ];

    /**
     * @return MessageFactory
     */
    protected static function newFactory()
    {
        return MessageFactory::new();
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'id', 'room_id');
    }
}

// end
