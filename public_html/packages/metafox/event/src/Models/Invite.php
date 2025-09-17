<?php

namespace MetaFox\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Event\Database\Factories\InviteFactory;
use MetaFox\Event\Notifications\Invite as InviteNotification;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Invite.
 *
 * @property int    $id
 * @property int    $status_id
 * @property int    $event_id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $owner_id
 * @property int    $owner_type
 * @property string $created_at
 * @property string $updated_at
 * @property Event  $event
 */
class Invite extends Model implements Entity, IsNotifyInterface, HasUrl
{
    use HasEntity;
    use HasOwnerMorph;
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'event_invite';

    public const STATUS_PENDING          = 0;
    public const STATUS_APPROVED         = 1;
    public const STATUS_NOT_INVITE_AGAIN = 2;
    public const STATUS_DECLINED         = 3;

    public const EXPIRE_DAY = 30; //Todo: update invite status to STATUS_DECLINED when expired => use queue

    protected $table = 'event_invites';

    protected $fillable = [
        'status_id',
        'event_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
    ];

    protected static function newFactory(): InviteFactory
    {
        return InviteFactory::new();
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id')->withTrashed();
    }

    /**
     * @return array<mixed>
     */
    public function toNotification(): ?array
    {
        if ($this->status_id != self::STATUS_PENDING) {
            return null;
        }

        return [$this->owner, new InviteNotification($this)];
    }

    public function toLink(): ?string
    {
        $event = $this->event;

        if (!$event instanceof HasUrl) {
            return url_utility()->makeApiUrl('events/invited');
        }

        return $event->toLink();
    }

    public function toUrl(): ?string
    {
        $event = $this->event;

        if (!$event instanceof HasUrl) {
            return url_utility()->makeApiUrl('events/invited');
        }

        return $event->toUrl();
    }

    public function toRouter(): ?string
    {
        $event = $this->event;

        if (!$event instanceof HasUrl) {
            return url_utility()->makeApiUrl('events/invited');
        }

        return $event->toRouter();
    }

    public function isPending(): bool
    {
        return $this->status_id == self::STATUS_PENDING;
    }
}
