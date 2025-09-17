<?php

namespace MetaFox\Group\Models;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Group\Database\Factories\MuteFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Mute.
 *
 * @property int    $id
 * @property mixed  $expired_at
 * @property int    $group_id
 * @property Group  $group
 * @method   static MuteFactory factory(...$parameters)
 */
class Mute extends Model implements Entity
{
    use HasUserMorph;
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE  = 'group_mute';
    public const STATUS_MUTED = 1;

    protected $table = 'group_muted';

    /** @var string[] */
    protected $fillable = [
        'id',
        'group_id',
        'user_id',
        'user_type',
        'status',
        'expired_at',
    ];

    /**
     * @return MuteFactory
     */
    protected static function newFactory()
    {
        return MuteFactory::new();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id')->withTrashed();
    }

    /**
     * @return bool
     * @throws AuthenticationException
     */
    public function isMuted(): bool
    {
        $context = user();

        return $this->hasMany(Group::class, 'group_id', 'id')
            ->where('status', self::STATUS_MUTED)
            ->where('user_id', $context->entityId())
            ->exists();
    }
}

// end
