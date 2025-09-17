<?php

namespace MetaFox\Notification\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use MetaFox\Notification\Database\Factories\NotificationFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Notification.
 * @mixin Builder
 *
 * @property        int               $id
 * @property        int               $notifiable_id
 * @property        string            $notifiable_type
 * @property        IsNotifiable|null $notifiable
 * @property        int               $item_id
 * @property        string            $item_type
 * @property        int               $data_item_id
 * @property        string            $data_item_type
 * @property        int               $user_id
 * @property        string            $user_type
 * @property        array             $data
 * @property        string            $type
 * @property        mixed             $updated_at
 * @property        mixed             $notified_at
 * @property        mixed             $created_at
 * @property        mixed             $read_at
 * @property        bool              $is_notified
 * @property        bool              $is_read
 * @method   static NotificationFactory factory(...$parameters)
 */
class Notification extends Model implements Entity
{
    use HasFactory;
    use HasEntity;
    use HasItemMorph;
    use HasUserMorph;
    use SoftDeletes;

    public const ENTITY_TYPE = 'notification';

    protected $keyType = 'string';

    protected $table = 'notifications';

    public $incrementing = true;

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'notifiable_id',
        'notifiable_type',
        'item_id',
        'item_type',
        'data_item_id',
        'data_item_type',
        'user_id',
        'user_type',
        'data',
        'type',
        'is_request',
        'notified_at',
        'read_at',
    ];

    /** @var string[] */
    protected $appends = [
        'is_notified',
        'is_read',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'data' => 'array',
    ];

    protected static function newFactory(): NotificationFactory
    {
        return NotificationFactory::new();
    }

    public function getIsNotifiedAttribute(): bool
    {
        return $this->notified_at !== null;
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * @return MorphTo
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo('notifiable', 'notifiable_type', 'notifiable_id')->withTrashed();
    }
}
