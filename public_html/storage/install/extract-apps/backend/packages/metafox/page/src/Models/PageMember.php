<?php

namespace MetaFox\Page\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Page\Database\Factories\PageMemberFactory;
use MetaFox\Page\Notifications\LikePageNotification;
use MetaFox\Platform\Contracts\HasShortcutItem;
use MetaFox\Platform\Contracts\IsActivitySubscriptionInterface;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class PageLike.
 * @property        User   $user
 * @property        Page   $page
 * @property        int    $user_id
 * @property        int    $page_id
 * @property        string $user_type
 * @property        int    $member_type
 * @method   static PageMemberFactory factory()
 */
class PageMember extends Model implements
    IsActivitySubscriptionInterface,
    IsPrivacyItemInterface,
    IsNotifyInterface,
    HasShortcutItem
{
    use HasEntity;
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'page_member';

    protected $table = 'page_members';

    public const MEMBER = 0;
    public const ADMIN = 1;

    public const LIKED = 1;
    public const NO_LIKE = 0;

    protected $fillable = [
        'page_id',
        'user_id',
        'user_type',
        'member_type',
    ];

    protected static function newFactory(): PageMemberFactory
    {
        return PageMemberFactory::new();
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id', 'id')->withTrashed();
    }

    public function toActivitySubscription(): array
    {
        return [$this->user_id, $this->page_id];
    }

    public function toPrivacyItem(): array
    {
        $abilities = [
            [$this->user_id, $this->page_id, Page::ENTITY_TYPE, Page::PAGE_MEMBERS],
        ];

        if ($this->member_type == self::ADMIN) {
            $abilities[] = [$this->user_id, $this->page_id, Page::ENTITY_TYPE, Page::PAGE_ADMINS];
        }

        return $abilities;
    }

    public function isAdminRole(): bool
    {
        return $this->member_type === self::ADMIN;
    }

    public function isMemberRole(): bool
    {
        return $this->member_type === self::MEMBER;
    }

    public function toNotification(): ?array
    {
        if ($this->userId() == $this->page->userId()) {
            return null;
        }

        $user = $this->page->admins()->get()
            ->collect()->pluck('user');

        return [$user, new LikePageNotification($this)];
    }

    public function toShortcutItem(): array
    {
        return [
            'item_id'   => $this->page->entityId(),
            'item_type' => $this->page->entityType(),
            'user_id'   => $this->userId(),
            'user_type' => $this->userType(),
        ];
    }
}
