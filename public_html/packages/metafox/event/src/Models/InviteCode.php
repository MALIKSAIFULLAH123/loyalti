<?php

namespace MetaFox\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Event\Database\Factories\InviteCodeFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class InviteCode.
 *
 * @property int    $id
 * @property int    $status_id
 * @property int    $event_id
 * @property int    $user_id
 * @property string $user_type
 * @property string $code
 * @property int    $status
 * @property string $created_at
 * @property string $expired_at
 * @property string $updated_at
 * @method   static InviteCodeFactory factory(...$parameters)
 */
class InviteCode extends Model implements Entity, HasUrl
{
    use HasUserMorph;
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE     = 'event_invite_code';
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE   = 1;

    protected $table = 'event_invite_codes';

    /** @var string[] */
    protected $fillable = [
        'event_id',
        'user_id',
        'user_type',
        'code',
        'status',
        'expired_at',
    ];

    /**
     * @return InviteCodeFactory
     */
    protected static function newFactory()
    {
        return InviteCodeFactory::new();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id')->withTrashed();
    }

    public function isActive(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("event/invite/{$this->code}");
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("event/invite/{$this->code}");
    }

    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl("event/invite/{$this->code}");
    }
}

// end
