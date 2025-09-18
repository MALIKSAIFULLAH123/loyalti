<?php

namespace MetaFox\Page\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MetaFox\Page\Database\Factories\PageInviteFactory;
use MetaFox\Page\Notifications\PageInvite as PageInviteNotification;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class PageInvite.
 * @property int    $page_id
 * @property int    $status_id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $owner_id
 * @property string $owner_type
 * @property User   $user
 * @property User   $owner
 * @property Page   $page
 * @property string $expired_at
 * @property string $invite_type
 */
class PageInvite extends Model implements Entity, IsNotifyInterface, HasUrl
{
    use HasEntity;
    use HasOwnerMorph;
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'page_invite';

    public const STATUS_PENDING          = 0;
    public const STATUS_APPROVED         = 1;
    public const STATUS_NOT_INVITE_AGAIN = 2;
    public const STATUS_NOT_USE          = 3;
    public const INVITE_MEMBER           = 'invite_member';
    public const INVITE_ADMIN            = 'invite_admin';

    public const EXPIRE_DAY = 30; //Todo: update invite status to STATUS_NOT_USE when expired => use queue

    protected $table = 'page_invites';

    protected $fillable = [
        'status_id',
        'page_id',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'expired_at',
        'invite_type',
    ];

    protected static function newFactory(): PageInviteFactory
    {
        return PageInviteFactory::new();
    }

    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id')->withTrashed();
    }

    public function owner(): MorphTo
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id')->withTrashed();
    }

    public function page(): HasOne
    {
        return $this->hasOne(Page::class, 'id', 'page_id')->withTrashed();
    }

    public function toNotification(): ?array
    {
        if ($this->status_id != self::STATUS_PENDING) {
            return null;
        }

        if (null !== $this->page && !$this->page->isApproved()) {
            return null;
        }

        return [$this->owner, new PageInviteNotification($this)];
    }

    public function toLink(): ?string
    {
        return $this->page->toLink();
    }

    public function toUrl(): ?string
    {
        return $this->page->toUrl();
    }

    public function toRouter(): ?string
    {
        return $this->page->toRouter();
    }

    public function getInviteType(): ?string
    {
        return $this->invite_type;
    }

    public function isInviteMember(): bool
    {
        return $this->getInviteType() == self::INVITE_MEMBER;
    }

    public function isInviteAdmin(): bool
    {
        return $this->getInviteType() == self::INVITE_ADMIN;
    }
}
