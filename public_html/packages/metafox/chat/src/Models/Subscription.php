<?php

namespace MetaFox\Chat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Chat\Database\Factories\SubscriptionFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Subscription.
 *
 * @property int $id
 * @property        int is_favourite
 * @property        int is_showed
 * @property        int is_deleted
 * @property        int is_block
 * @method static SubscriptionFactory factory(...$parameters)
 */
class Subscription extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'chat_subscription';

    protected $table = 'chat_subscriptions';

    /** @var string[] */
    protected $fillable = [
        'room_id',
        'user_id',
        'user_type',
        'subscription',
        'name',
        'total_unseen',
        'is_favourite',
        'is_showed',
        'is_deleted',
        'rejoin_at',
        'is_block',
        'is_seen_notification',
    ];

    protected $casts = [
        'is_showed'    => 'boolean',
        'is_deleted'   => 'boolean',
        'is_block'     => 'boolean',
        'is_favourite' => 'boolean',
    ];

    /**
     * @return SubscriptionFactory
     */
    protected static function newFactory()
    {
        return SubscriptionFactory::new();
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'id', 'room_id');
    }
}

// end
