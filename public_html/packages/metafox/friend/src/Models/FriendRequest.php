<?php

namespace MetaFox\Friend\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Database\Factories\FriendRequestFactory;
use MetaFox\Friend\Notifications\FriendRequested;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class FriendRequest.
 * @mixin Builder
 * @property int    $id
 * @property int    $status_id
 * @property int    $is_deny
 * @property User   $user
 * @property User   $owner
 * @property bool   $is_seen
 * @property bool   $is_ignore
 * @property string $created_at
 * @property string $updated_at
 * @method   static FriendRequestFactory factory()
 */
class FriendRequest extends Model implements Entity, IsNotifyInterface, HasUrl
{
    use HasEntity;
    use HasOwnerMorph;
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE    = 'friend_request';
    public const ACTION_APPROVE = 'approve';
    public const ACTION_DENY    = 'deny';
    public const IS_DENY        = 1;
    public const IS_SEEN        = 1;

    protected $table = 'friend_requests';

    protected $fillable = ['user_id', 'user_type', 'owner_type', 'owner_id', 'status_id', 'is_deny'];

    /** @var string[] */
    protected $appends = [
        'is_seen',
        'is_ignore',
    ];

    /**
     * @return array<int, mixed>
     */
    public function toNotification(): array
    {
        return [$this->owner, new FriendRequested($this)];
    }

    protected static function newFactory(): FriendRequestFactory
    {
        return FriendRequestFactory::new();
    }

    public function getIsSeenAttribute(): bool
    {
        return $this->status_id === self::IS_SEEN;
    }

    public function getIsIgnoreAttribute(): bool
    {
        return $this->is_deny === self::IS_DENY;
    }

    public function toLink(): ?string
    {
        $user = $this->user;

        if (!$user instanceof User) {
            return null;
        }

        return $user->toLink();
    }

    public function toUrl(): ?string
    {
        $user = $this->user;

        if (!$user instanceof User) {
            return null;
        }

        return $user->toUrl();
    }

    public function toRouter(): ?string
    {
        $user = $this->user;

        if (!$user instanceof User) {
            return null;
        }

        return $user->toRouter();
    }
}
