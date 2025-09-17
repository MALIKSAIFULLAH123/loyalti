<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Group\Database\Factories\MemberFactory;
use MetaFox\Platform\Contracts\HasShortcutItem;
use MetaFox\Platform\Contracts\IsActivitySubscriptionInterface;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Member.
 * @mixin Builder
 * @property        int    $id
 * @property        int    $group_id
 * @property        int    $user_id
 * @property        string $user_type
 * @property        string $member_type
 * @property        string $created_at
 * @property        Group  $group
 * @method   static MemberFactory factory(...$parameters)
 */
class Member extends Model implements
    IsActivitySubscriptionInterface,
    IsPrivacyItemInterface,
    HasShortcutItem
{
    use HasEntity;
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'group_member';

    public const JOINED = 1;
    public const REQUESTED = 2;
    protected $table = 'group_members';

    protected $fillable = [
        'id',
        'group_id',
        'user_id',
        'user_type',
        'member_type',
    ];

    public const MEMBER = 0;
    public const ADMIN = 1;
    public const MODERATOR = 2;

    protected static function newFactory(): MemberFactory
    {
        return MemberFactory::new();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id')->withTrashed();
    }

    public function toActivitySubscription(): array
    {
        return [$this->user_id, $this->group_id];
    }

    public function toPrivacyItem(): array
    {
        $abilities = [
            [$this->user_id, $this->group_id, Group::ENTITY_TYPE, Group::GROUP_MEMBERS],
        ];

        if (in_array($this->member_type, [self::ADMIN, self::MODERATOR])) {
            $abilities[] = [$this->user_id, $this->group_id, Group::ENTITY_TYPE, Group::GROUP_MODERATORS];
        }

        if ($this->member_type == self::ADMIN) {
            $abilities[] = [$this->user_id, $this->group_id, Group::ENTITY_TYPE, Group::GROUP_ADMINS];
        }

        return $abilities;
    }

    public function isAdminRole(): bool
    {
        return $this->member_type == self::ADMIN;
    }

    public function isModeratorRole(): bool
    {
        return $this->member_type == self::MODERATOR;
    }

    public function isMemberRole(): bool
    {
        return $this->member_type == self::MEMBER;
    }

    public function toShortcutItem(): array
    {
        return [
            'item_id'   => $this->group->entityId(),
            'item_type' => $this->group->entityType(),
            'user_id'   => $this->userId(),
            'user_type' => $this->userType(),
        ];
    }
}
