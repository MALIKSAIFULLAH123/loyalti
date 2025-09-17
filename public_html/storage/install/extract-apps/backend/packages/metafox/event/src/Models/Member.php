<?php

namespace MetaFox\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Event\Database\Factories\MemberFactory;
use MetaFox\Event\Notifications\EventRsvpNotification;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\IsActivitySubscriptionInterface;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Member.
 *
 * @property int    $id
 * @property int    $event_id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $rsvp_id
 * @property int    $role_id
 * @property string $created_at
 * @property string $updated_at
 * @property Event  $event
 */
class Member extends Model implements Entity, IsActivitySubscriptionInterface, IsPrivacyItemInterface, IsNotifyInterface
{
    use HasEntity;
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'event_member';

    public const NOT_INTERESTED = 0;
    public const JOINED = 1;
    public const INTERESTED = 2;
    public const INVITED = 3;

    protected $table = 'event_members';

    protected $fillable = [
        'id',
        'event_id',
        'user_id',
        'user_type',
        'role_id',
        'rsvp_id',
    ];

    public const ROLE_MEMBER = 0;
    public const ROLE_HOST = 1;

    protected static function newFactory(): MemberFactory
    {
        return MemberFactory::new();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id')->withTrashed();
    }

    public function toActivitySubscription(): array
    {
        return [$this->event_id, $this->event_id];
    }

    public function isRsvp(int $rsvp): bool
    {
        return $this->rsvp_id == $rsvp;
    }

    public function isRole(int $role): bool
    {
        return $this->role_id == $role;
    }

    public function hasHostPrivileges(): bool
    {
        return $this->isRsvp(self::JOINED) && $this->isRole(self::ROLE_HOST);
    }

    public function hasMemberPrivileges(): bool
    {
        // rule: interested users should also be considered as attendees
        if ($this->isRsvp(self::JOINED)) {
            return true;
        }

        return $this->isRsvp(self::INTERESTED);
    }

    public function toPrivacyItem(): array
    {
        $abilities = [];

        if ($this->hasHostPrivileges()) {
            $abilities[] = [$this->userId(), $this->event_id, Event::ENTITY_TYPE, Event::EVENT_HOSTS];
        }

        if ($this->hasMemberPrivileges()) {
            $abilities[] = [$this->userId(), $this->event_id, Event::ENTITY_TYPE, Event::EVENT_MEMBERS];
        }

        return $abilities;
    }

    public function toNotification(): ?array
    {
        $notifiables = [];
        if ($this->event->userId() == $this->userId() || $this->rsvp_id == self::NOT_INTERESTED) {
            return null;
        }

        if ($this->hasHostPrivileges()) {
            return null;
        }

        $authorizers = $this->event->authorizers()
            ->with(['user'])
            ->get();

        foreach ($authorizers as $authorizer) {
            $notifiables[] = $authorizer->user;
        }

        return [$notifiables, new EventRsvpNotification($this)];
    }
}
