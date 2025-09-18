<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Group\Database\Factories\InviteFactory;
use MetaFox\Group\Notifications\AddGroupAdmin;
use MetaFox\Group\Notifications\AddGroupModerator;
use MetaFox\Group\Notifications\GroupInvite as GroupInviteNotification;
use MetaFox\Group\Support\InviteType;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Invite.
 * @property int        $status_id
 * @property int        $group_id
 * @property int        $user_id
 * @property int        $owner_id
 * @property string     $user_type
 * @property string     $owner_type
 * @property User       $user
 * @property User       $owner
 * @property Group|null $group
 * @property string     $expired_at
 * @property string     $invite_type
 * @property string     $code
 * @property string     $created_at
 * @property string     $updated_at
 * @method   static InviteFactory factory(...$parameters)
 */
class Invite extends Model implements
    Entity,
    IsNotifyInterface,
    HasUrl
{
    use HasEntity;
    use HasOwnerMorph;
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'group_invite';

    public const STATUS_PENDING          = 0;
    public const STATUS_APPROVED         = 1;
    public const STATUS_NOT_INVITE_AGAIN = 2;
    public const STATUS_NOT_USE          = 3;
    public const STATUS_CANCELLED        = 4;
    public const STATUS_EXPIRED          = 5;

    protected $table = 'group_invites';

    protected $fillable = [
        'status_id',
        'group_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'expired_at',
        'invite_type',
        'code',
    ];

    protected static function newFactory(): InviteFactory
    {
        return InviteFactory::new();
    }

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id')->withTrashed();
    }

    public function toNotification(): ?array
    {
        if ($this->code !== null) {
            return null;
        }

        /*
         * In case group is existed, we need to check if relation group is not null
         * In case deleted notification of Invite, does not need checking that
         */
        if (null !== $this->group && !$this->group->isApproved()) {
            return null;
        }

        $notification = match ($this->getInviteType()) {
            InviteType::INVITED_MODERATOR_GROUP => new AddGroupModerator($this),
            InviteType::INVITED_ADMIN_GROUP     => new AddGroupAdmin($this),
            default                             => new GroupInviteNotification($this),
        };

        return [$this->owner, $notification];
    }

    public function toLink(): ?string
    {
        $group = $this->group;

        if (!$group instanceof HasUrl) {
            return url_utility()->makeApiUrl('group/invited');
        }

        return $group->toLink();
    }

    public function toUrl(): ?string
    {
        $group = $this->group;

        if (!$group instanceof HasUrl) {
            return url_utility()->makeApiUrl('group/invited');
        }

        return $group->toUrl();
    }

    public function toRouter(): ?string
    {
        $group = $this->group;

        if (!$group instanceof HasUrl) {
            return url_utility()->makeApiUrl('group/invited');
        }

        return $group->toRouter();
    }

    public function getInviteType(): ?string
    {
        return $this->invite_type;
    }

    public function isInviteMember(): bool
    {
        return $this->getInviteType() == InviteType::INVITED_MEMBER;
    }

    public function isInviteModerator(): bool
    {
        return $this->getInviteType() == InviteType::INVITED_MODERATOR_GROUP;
    }

    public function isInviteAdmin(): bool
    {
        return $this->getInviteType() == InviteType::INVITED_ADMIN_GROUP;
    }

    public function isInviteLink(): bool
    {
        return $this->getInviteType() == InviteType::INVITED_GENERATE_LINK;
    }

    public function isExpired(): bool
    {
        if ($this->status_id == self::STATUS_EXPIRED) {
            return true;
        }

        if ($this->status_id != self::STATUS_PENDING) {
            return false;
        }

        if ($this->expired_at == null) {
            return false;
        }

        return !calculatorExpiredDay($this->expired_at);
    }

    public function isPending(): bool
    {
        return $this->status_id == self::STATUS_PENDING && !$this->isExpired();
    }
}
